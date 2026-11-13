<?php

declare(strict_types=1);

/*
 * Widen Author.Password so it can hold password_hash() output.
 *
 * The legacy column held 32-character MD5 hashes. Existing values are kept
 * (they are upgraded one by one as users log in). Nullability is preserved,
 * and nothing happens if the column is already wide enough.
 */
return static function (PDO $pdo): void {
    $column = $pdo->query(
        "SELECT CHARACTER_MAXIMUM_LENGTH AS max_length, IS_NULLABLE AS nullable
         FROM information_schema.COLUMNS
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Author' AND COLUMN_NAME = 'Password'",
    )->fetch(PDO::FETCH_ASSOC);

    if (!is_array($column)) {
        throw new RuntimeException('Column Author.Password was not found.');
    }
    if ((int) $column['max_length'] >= 255) {
        return;
    }

    $null = $column['nullable'] === 'YES' ? 'NULL' : 'NOT NULL';
    $pdo->exec("ALTER TABLE Author MODIFY Password VARCHAR(255) {$null}");
};
