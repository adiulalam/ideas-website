<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class VoteRepository extends Repository
{
    /** Record an author's vote (+1 or -1) on an idea, replacing any previous vote. */
    public function cast(int $ideaId, int $authorId, int $value): void
    {
        $value = $value > 0 ? 1 : -1;
        $this->db->transaction(function (PDO $pdo) use ($ideaId, $authorId, $value): void {
            $this->run('DELETE FROM Vote WHERE IdeaID = :idea AND AuthorID = :author', ['idea' => $ideaId, 'author' => $authorId], $pdo);
            $this->run('INSERT INTO Vote (IdeaID, AuthorID, VoteNumber) VALUES (:idea, :author, :vote)', ['idea' => $ideaId, 'author' => $authorId, 'vote' => $value], $pdo);
        });
    }

    /**
     * @param list<int> $ideaIds
     * @return array<int, int> idea ID => the author's vote (+1 / -1)
     */
    public function forAuthor(int $authorId, array $ideaIds): array
    {
        if ($ideaIds === []) {
            return [];
        }

        $stmt = $this->run(
            'SELECT IdeaID, VoteNumber FROM Vote WHERE AuthorID = ? AND IdeaID IN (' . self::placeholders(count($ideaIds)) . ')',
            array_merge([$authorId], $ideaIds),
        );
        $votes = [];
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $votes[(int) $row['IdeaID']] = (int) $row['VoteNumber'];
        }

        return $votes;
    }
}
