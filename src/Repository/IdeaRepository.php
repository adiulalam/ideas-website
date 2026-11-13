<?php

declare(strict_types=1);

namespace App\Repository;

use App\Ideas\IdeaFilters;
use PDO;

final class IdeaRepository extends Repository
{
    private const SELECT = 'SELECT i.ID, i.IdeaText, i.IdeaDate, i.Image, i.AuthorID, a.Name AS AuthorName,
            (SELECT COALESCE(SUM(v.VoteNumber), 0) FROM Vote v WHERE v.IdeaID = i.ID) AS Votes,
            (SELECT COUNT(*) FROM Comment c WHERE c.IdeaID = i.ID) AS CommentCount
        FROM Idea i
        INNER JOIN Author a ON a.Author_ID = i.AuthorID';

    /** @return list<array<string, mixed>> */
    public function search(IdeaFilters $filters, int $limit, int $offset): array
    {
        $params = [];
        $sql = self::SELECT . $this->where($filters, $params)
            . ' ORDER BY ' . $filters->orderBy()
            . ' LIMIT :limit OFFSET :offset';
        $params['limit'] = max(1, $limit);
        $params['offset'] = max(0, $offset);

        return array_map(self::normalise(...), $this->select($sql, $params));
    }

    public function count(IdeaFilters $filters): int
    {
        $params = [];
        $sql = 'SELECT COUNT(*) FROM Idea i INNER JOIN Author a ON a.Author_ID = i.AuthorID' . $this->where($filters, $params);

        return (int) $this->run($sql, $params)->fetchColumn();
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $row = $this->selectOne(
            str_replace('a.Name AS AuthorName,', 'a.Name AS AuthorName, a.Email AS AuthorEmail,', self::SELECT) . ' WHERE i.ID = :id',
            ['id' => $id],
        );

        return $row === null ? null : self::normalise($row);
    }

    /** @return list<int> */
    public function categoryIds(int $ideaId): array
    {
        return array_map('intval', $this->run('SELECT CategoryID FROM IdeaCategory WHERE IdeaID = :id', ['id' => $ideaId])->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @return list<int> */
    public function departmentIds(int $ideaId): array
    {
        return array_map('intval', $this->run('SELECT DepartmentID FROM IdeaDepartment WHERE IdeaID = :id', ['id' => $ideaId])->fetchAll(PDO::FETCH_COLUMN));
    }

    /**
     * @param list<int> $categoryIds
     * @param list<int> $departmentIds
     */
    public function create(int $authorId, string $text, string $image, array $categoryIds, array $departmentIds): int
    {
        return $this->db->transaction(function (PDO $pdo) use ($authorId, $text, $image, $categoryIds, $departmentIds): int {
            $this->run(
                'INSERT INTO Idea (IdeaText, IdeaDate, AuthorID, Image) VALUES (:text, CURDATE(), :author, :image)',
                ['text' => $text, 'author' => $authorId, 'image' => $image],
                $pdo,
            );
            $id = (int) $pdo->lastInsertId();
            $this->syncLinks($pdo, $id, $categoryIds, $departmentIds);

            return $id;
        });
    }

    /**
     * @param string|null $image New image value, or null to keep the current one.
     * @param list<int> $categoryIds
     * @param list<int> $departmentIds
     */
    public function update(int $id, string $text, ?string $image, array $categoryIds, array $departmentIds): void
    {
        $this->db->transaction(function (PDO $pdo) use ($id, $text, $image, $categoryIds, $departmentIds): void {
            if ($image === null) {
                $this->run('UPDATE Idea SET IdeaText = :text WHERE ID = :id', ['text' => $text, 'id' => $id], $pdo);
            } else {
                $this->run('UPDATE Idea SET IdeaText = :text, Image = :image WHERE ID = :id', ['text' => $text, 'image' => $image, 'id' => $id], $pdo);
            }
            $this->syncLinks($pdo, $id, $categoryIds, $departmentIds);
        });
    }

    /** Delete an idea and everything attached to it. Returns its Image value for file clean-up. */
    public function delete(int $id): ?string
    {
        return $this->db->transaction(static function (PDO $pdo) use ($id): ?string {
            $stmt = $pdo->prepare('SELECT Image FROM Idea WHERE ID = :id');
            $stmt->execute(['id' => $id]);
            $image = $stmt->fetchColumn();

            self::deleteIdeas($pdo, [$id]);

            return is_string($image) ? $image : null;
        });
    }

    /**
     * Remove ideas plus their categories, departments, comments and votes.
     * Shared with AuthorRepository::delete().
     *
     * @param list<int> $ideaIds
     */
    public static function deleteIdeas(PDO $pdo, array $ideaIds): void
    {
        if ($ideaIds === []) {
            return;
        }

        $in = self::placeholders(count($ideaIds));
        foreach (['IdeaCategory', 'IdeaDepartment', 'Comment', 'Vote'] as $table) {
            $pdo->prepare("DELETE FROM {$table} WHERE IdeaID IN ({$in})")->execute($ideaIds);
        }
        $pdo->prepare("DELETE FROM Idea WHERE ID IN ({$in})")->execute($ideaIds);
    }

    /**
     * @param list<int> $categoryIds
     * @param list<int> $departmentIds
     */
    private function syncLinks(PDO $pdo, int $ideaId, array $categoryIds, array $departmentIds): void
    {
        $this->run('DELETE FROM IdeaCategory WHERE IdeaID = :id', ['id' => $ideaId], $pdo);
        $insert = $pdo->prepare('INSERT INTO IdeaCategory (IdeaID, CategoryID) VALUES (:idea, :category)');
        foreach ($categoryIds as $categoryId) {
            $insert->execute(['idea' => $ideaId, 'category' => $categoryId]);
        }

        $this->run('DELETE FROM IdeaDepartment WHERE IdeaID = :id', ['id' => $ideaId], $pdo);
        $insert = $pdo->prepare('INSERT INTO IdeaDepartment (IdeaID, DepartmentID) VALUES (:idea, :department)');
        foreach ($departmentIds as $departmentId) {
            $insert->execute(['idea' => $ideaId, 'department' => $departmentId]);
        }
    }

    /** @param array<string, scalar|null> $params */
    private function where(IdeaFilters $filters, array &$params): string
    {
        $sql = ' WHERE 1 = 1';
        if ($filters->authorId !== null) {
            $sql .= ' AND i.AuthorID = :author';
            $params['author'] = $filters->authorId;
        }
        if ($filters->categoryId !== null) {
            $sql .= ' AND EXISTS (SELECT 1 FROM IdeaCategory ic WHERE ic.IdeaID = i.ID AND ic.CategoryID = :category)';
            $params['category'] = $filters->categoryId;
        }
        if ($filters->search !== '') {
            $sql .= ' AND i.IdeaText LIKE :search';
            $params['search'] = '%' . addcslashes($filters->search, '%_\\') . '%';
        }

        return $sql;
    }

    /**
     * @param array<string, mixed> $row
     * @return array<string, mixed>
     */
    private static function normalise(array $row): array
    {
        $row['ID'] = (int) $row['ID'];
        $row['AuthorID'] = (int) $row['AuthorID'];
        $row['Votes'] = (int) $row['Votes'];
        $row['CommentCount'] = (int) $row['CommentCount'];
        $row['IdeaText'] = (string) $row['IdeaText'];
        $row['Image'] = (string) ($row['Image'] ?? '');

        return $row;
    }
}
