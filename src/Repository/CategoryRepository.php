<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class CategoryRepository extends Repository
{
    /** @return list<array{ID: int, Name: string, Image: string}> */
    public function all(): array
    {
        return array_map(
            static fn (array $row): array => ['ID' => (int) $row['ID'], 'Name' => (string) $row['Name'], 'Image' => (string) ($row['Image'] ?? '')],
            $this->select('SELECT ID, Name, Image FROM Category ORDER BY Name'),
        );
    }

    /** @return array{ID: int, Name: string, Image: string}|null */
    public function find(int $id): ?array
    {
        $row = $this->selectOne('SELECT ID, Name, Image FROM Category WHERE ID = :id', ['id' => $id]);

        return $row === null ? null : ['ID' => (int) $row['ID'], 'Name' => (string) $row['Name'], 'Image' => (string) ($row['Image'] ?? '')];
    }

    /**
     * Filter submitted IDs down to ones that exist.
     *
     * @param list<int> $ids
     * @return list<int>
     */
    public function existingIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return array_map('intval', $this->run('SELECT ID FROM Category WHERE ID IN (' . self::placeholders(count($ids)) . ')', $ids)->fetchAll(PDO::FETCH_COLUMN));
    }

    public function nameExists(string $name, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM Category WHERE Name = :name';
        $params = ['name' => $name];
        if ($exceptId !== null) {
            $sql .= ' AND ID <> :id';
            $params['id'] = $exceptId;
        }

        return (int) $this->run($sql, $params)->fetchColumn() > 0;
    }

    public function create(string $name): int
    {
        $this->run('INSERT INTO Category (Name) VALUES (:name)', ['name' => $name]);

        return (int) $this->pdo()->lastInsertId();
    }

    public function update(int $id, string $name): void
    {
        $this->run('UPDATE Category SET Name = :name WHERE ID = :id', ['name' => $name, 'id' => $id]);
    }

    public function delete(int $id): void
    {
        $this->db->transaction(function (PDO $pdo) use ($id): void {
            $this->run('DELETE FROM IdeaCategory WHERE CategoryID = :id', ['id' => $id], $pdo);
            $this->run('DELETE FROM Category WHERE ID = :id', ['id' => $id], $pdo);
        });
    }
}
