<?php

declare(strict_types=1);

namespace App\Migrations;

use PDO;
use RuntimeException;

/**
 * Applies PHP migrations from database/migrations in filename order and
 * records them in the SchemaMigration table. Each migration file returns a
 * `callable(PDO): void` and must be safe to run against the legacy schema.
 */
final class Migrator
{
    public function __construct(private readonly PDO $pdo, private readonly string $directory)
    {
    }

    /** @return list<string> Log lines. */
    public function run(): array
    {
        $this->pdo->exec(
            'CREATE TABLE IF NOT EXISTS SchemaMigration (
                Version   VARCHAR(191) NOT NULL PRIMARY KEY,
                AppliedAt DATETIME     NOT NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
        );

        $applied = array_flip(array_map('strval', $this->pdo->query('SELECT Version FROM SchemaMigration')->fetchAll(PDO::FETCH_COLUMN)));
        $files = glob($this->directory . '/*.php') ?: [];
        sort($files, SORT_STRING);

        $log = [];
        foreach ($files as $file) {
            $version = basename($file, '.php');
            if (isset($applied[$version])) {
                continue;
            }

            $migration = require $file;
            if (!is_callable($migration)) {
                throw new RuntimeException("Migration {$version} must return a callable.");
            }

            // MySQL DDL auto-commits, so migrations are not wrapped in a transaction.
            $migration($this->pdo);
            $this->pdo->prepare('INSERT INTO SchemaMigration (Version, AppliedAt) VALUES (:version, NOW())')->execute(['version' => $version]);
            $log[] = "applied {$version}";
        }

        if ($log === []) {
            $log[] = 'nothing to migrate';
        }

        return $log;
    }
}
