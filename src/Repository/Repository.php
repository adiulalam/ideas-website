<?php

declare(strict_types=1);

namespace App\Repository;

use App\Database;
use PDO;
use PDOStatement;

/**
 * Base class for data access. All SQL lives in repositories and every value
 * is bound as a parameter — never interpolate request data into SQL.
 */
abstract class Repository
{
    public function __construct(protected readonly Database $db)
    {
    }

    protected function pdo(): PDO
    {
        return $this->db->pdo();
    }

    /** @param array<int|string, scalar|null> $params */
    protected function run(string $sql, array $params = [], ?PDO $pdo = null): PDOStatement
    {
        $stmt = ($pdo ?? $this->pdo())->prepare($sql);
        foreach ($params as $key => $value) {
            $stmt->bindValue(
                is_int($key) ? $key + 1 : ':' . ltrim($key, ':'),
                $value,
                match (true) {
                    is_int($value) => PDO::PARAM_INT,
                    is_bool($value) => PDO::PARAM_BOOL,
                    $value === null => PDO::PARAM_NULL,
                    default => PDO::PARAM_STR,
                },
            );
        }
        $stmt->execute();

        return $stmt;
    }

    /**
     * @param array<int|string, scalar|null> $params
     * @return list<array<string, mixed>>
     */
    protected function select(string $sql, array $params = []): array
    {
        /** @var list<array<string, mixed>> */
        return $this->run($sql, $params)->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param array<int|string, scalar|null> $params
     * @return array<string, mixed>|null
     */
    protected function selectOne(string $sql, array $params = []): ?array
    {
        $row = $this->run($sql, $params)->fetch(PDO::FETCH_ASSOC);

        return is_array($row) ? $row : null;
    }

    /** "?, ?, ?" for an IN (...) list. */
    protected static function placeholders(int $count): string
    {
        return implode(', ', array_fill(0, max(1, $count), '?'));
    }
}
