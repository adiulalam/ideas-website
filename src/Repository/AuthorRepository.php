<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class AuthorRepository extends Repository
{
    /** Minimum column width needed to store password_hash() output. */
    private const PASSWORD_COLUMN_MIN = 60;

    private ?bool $passwordColumnFits = null;

    /** @return array<string, mixed>|null Author (without password) plus `roles`. */
    public function findWithRoles(int $id): ?array
    {
        $author = $this->find($id);
        if ($author === null) {
            return null;
        }
        $author['roles'] = $this->roles($id);

        return $author;
    }

    /** @return array<string, mixed>|null */
    public function find(int $id): ?array
    {
        $row = $this->selectOne('SELECT Author_ID, Name, Email, Author_Image FROM Author WHERE Author_ID = :id', ['id' => $id]);
        if ($row !== null) {
            $row['Author_ID'] = (int) $row['Author_ID'];
        }

        return $row;
    }

    /** @return array<string, mixed>|null Includes the password hash — never pass to templates. */
    public function findByEmail(string $email): ?array
    {
        return $this->selectOne('SELECT Author_ID, Name, Email, Password FROM Author WHERE Email = :email LIMIT 1', ['email' => $email]);
    }

    public function emailExists(string $email, ?int $exceptId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM Author WHERE Email = :email';
        $params = ['email' => $email];
        if ($exceptId !== null) {
            $sql .= ' AND Author_ID <> :id';
            $params['id'] = $exceptId;
        }

        return (int) $this->run($sql, $params)->fetchColumn() > 0;
    }

    /** @return list<string> */
    public function roles(int $authorId): array
    {
        return array_map('strval', $this->run('SELECT RoleID FROM AuthorRole WHERE AuthorID = :id', ['id' => $authorId])->fetchAll(PDO::FETCH_COLUMN));
    }

    /** @return list<array<string, mixed>> Authors with a `roles` list each. */
    public function allWithRoles(): array
    {
        $authors = $this->select('SELECT Author_ID, Name, Email, Author_Image FROM Author ORDER BY Name, Author_ID');
        $roles = [];
        foreach ($this->select('SELECT AuthorID, RoleID FROM AuthorRole') as $row) {
            $roles[(int) $row['AuthorID']][] = (string) $row['RoleID'];
        }
        foreach ($authors as &$author) {
            $author['Author_ID'] = (int) $author['Author_ID'];
            $author['roles'] = $roles[$author['Author_ID']] ?? [];
        }

        return $authors;
    }

    /** @return list<array{ID: int, Name: string}> For the "filter by author" dropdown. */
    public function listForFilter(): array
    {
        return array_map(
            static fn (array $row): array => ['ID' => (int) $row['Author_ID'], 'Name' => (string) $row['Name']],
            $this->select('SELECT Author_ID, Name FROM Author ORDER BY Name'),
        );
    }

    /** @param list<string> $roles */
    public function create(string $name, string $email, string $passwordHash, array $roles): int
    {
        $this->assertPasswordColumnFits();

        return $this->db->transaction(function (PDO $pdo) use ($name, $email, $passwordHash, $roles): int {
            $this->run(
                'INSERT INTO Author (Name, Email, Password) VALUES (:name, :email, :password)',
                ['name' => $name, 'email' => $email, 'password' => $passwordHash],
                $pdo,
            );
            $id = (int) $pdo->lastInsertId();
            $this->replaceRoles($pdo, $id, $roles);

            return $id;
        });
    }

    /**
     * @param string|null $passwordHash New hash, or null to keep the current password.
     * @param list<string> $roles
     */
    public function update(int $id, string $name, string $email, ?string $passwordHash, array $roles): void
    {
        if ($passwordHash !== null) {
            $this->assertPasswordColumnFits();
        }

        $this->db->transaction(function (PDO $pdo) use ($id, $name, $email, $passwordHash, $roles): void {
            $this->run('UPDATE Author SET Name = :name, Email = :email WHERE Author_ID = :id', ['name' => $name, 'email' => $email, 'id' => $id], $pdo);
            if ($passwordHash !== null) {
                $this->run('UPDATE Author SET Password = :password WHERE Author_ID = :id', ['password' => $passwordHash, 'id' => $id], $pdo);
            }
            $this->replaceRoles($pdo, $id, $roles);
        });
    }

    public function updatePasswordHash(int $id, string $hash): void
    {
        $this->run('UPDATE Author SET Password = :password WHERE Author_ID = :id', ['password' => $hash, 'id' => $id]);
    }

    /**
     * Delete an author and all of their content.
     *
     * @return list<string> Image values of the deleted ideas, for file clean-up.
     */
    public function delete(int $id): array
    {
        return $this->db->transaction(static function (PDO $pdo) use ($id): array {
            $stmt = $pdo->prepare('SELECT ID, Image FROM Idea WHERE AuthorID = :id');
            $stmt->execute(['id' => $id]);
            $ideaIds = [];
            $images = [];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
                $ideaIds[] = (int) $row['ID'];
                if (is_string($row['Image']) && $row['Image'] !== '') {
                    $images[] = $row['Image'];
                }
            }

            IdeaRepository::deleteIdeas($pdo, $ideaIds);
            foreach (['DELETE FROM Comment WHERE AuthorID = :id', 'DELETE FROM Vote WHERE AuthorID = :id', 'DELETE FROM AuthorRole WHERE AuthorID = :id', 'DELETE FROM Author WHERE Author_ID = :id'] as $sql) {
                $pdo->prepare($sql)->execute(['id' => $id]);
            }

            return $images;
        });
    }

    /**
     * Legacy databases store 32-character MD5 hashes and may have a column
     * too narrow for password_hash() output. Writing a modern hash there
     * would be truncated or rejected, so check first (cached per request).
     */
    public function passwordColumnFits(): bool
    {
        if ($this->passwordColumnFits === null) {
            $length = $this->run(
                "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.COLUMNS
                 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Author' AND COLUMN_NAME = 'Password'",
            )->fetchColumn();
            $this->passwordColumnFits = $length !== false && (int) $length >= self::PASSWORD_COLUMN_MIN;
        }

        return $this->passwordColumnFits;
    }

    private function assertPasswordColumnFits(): void
    {
        if (!$this->passwordColumnFits()) {
            throw new \RuntimeException('Author.Password is too narrow for modern hashes. Run `php bin/migrate.php`.');
        }
    }

    /** @param list<string> $roles */
    private function replaceRoles(PDO $pdo, int $authorId, array $roles): void
    {
        $this->run('DELETE FROM AuthorRole WHERE AuthorID = :id', ['id' => $authorId], $pdo);
        $insert = $pdo->prepare('INSERT INTO AuthorRole (AuthorID, RoleID) VALUES (:author, :role)');
        foreach ($roles as $role) {
            $insert->execute(['author' => $authorId, 'role' => $role]);
        }
    }
}
