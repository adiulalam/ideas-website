<?php

declare(strict_types=1);

namespace App\Repository;

final class CommentRepository extends Repository
{
    /** @return list<array<string, mixed>> Newest first. */
    public function forIdea(int $ideaId): array
    {
        $rows = $this->select(
            'SELECT c.CommentID, c.Comment, c.Time, c.AuthorID, a.Name AS AuthorName
             FROM Comment c
             INNER JOIN Author a ON a.Author_ID = c.AuthorID
             WHERE c.IdeaID = :id
             ORDER BY c.Time DESC, c.CommentID DESC',
            ['id' => $ideaId],
        );
        foreach ($rows as &$row) {
            $row['CommentID'] = (int) $row['CommentID'];
            $row['AuthorID'] = (int) $row['AuthorID'];
        }

        return $rows;
    }

    public function create(int $ideaId, int $authorId, string $comment): void
    {
        $this->run(
            'INSERT INTO Comment (Comment, IdeaID, AuthorID) VALUES (:comment, :idea, :author)',
            ['comment' => $comment, 'idea' => $ideaId, 'author' => $authorId],
        );
    }

    /** Delete a comment only if it belongs to $authorId. Returns the comment's idea ID, or null. */
    public function deleteOwned(int $commentId, int $authorId): ?int
    {
        $row = $this->selectOne('SELECT IdeaID FROM Comment WHERE CommentID = :id AND AuthorID = :author', ['id' => $commentId, 'author' => $authorId]);
        if ($row === null) {
            return null;
        }
        $this->run('DELETE FROM Comment WHERE CommentID = :id AND AuthorID = :author', ['id' => $commentId, 'author' => $authorId]);

        return (int) $row['IdeaID'];
    }
}
