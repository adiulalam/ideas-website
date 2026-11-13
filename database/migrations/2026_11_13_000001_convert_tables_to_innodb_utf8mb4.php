<?php

declare(strict_types=1);

/*
 * Convert legacy tables to InnoDB and utf8mb4.
 *
 * Production's hand-made tables were MyISAM, which silently ignores
 * transactions (so a failed multi-step write could half-apply), with a mix of
 * latin1 and utf8mb3 character sets (no emoji; unsupported characters were
 * stored as "?"). Converting is lossless for existing text: MySQL re-encodes
 * every value. Idempotent: tables already in the target state are skipped.
 */
return static function (PDO $pdo): void {
    $tables = $pdo->query(
        "SELECT TABLE_NAME, ENGINE, TABLE_COLLATION
         FROM information_schema.TABLES
         WHERE TABLE_SCHEMA = DATABASE() AND TABLE_TYPE = 'BASE TABLE'",
    )->fetchAll(PDO::FETCH_ASSOC);

    foreach ($tables as $table) {
        $name = '`' . str_replace('`', '``', (string) $table['TABLE_NAME']) . '`';

        if (strcasecmp((string) $table['ENGINE'], 'InnoDB') !== 0) {
            $pdo->exec("ALTER TABLE {$name} ENGINE=InnoDB");
        }
        if (!str_starts_with((string) $table['TABLE_COLLATION'], 'utf8mb4')) {
            $pdo->exec("ALTER TABLE {$name} CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        }
    }

    $pdo->exec('ALTER DATABASE CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci');
};
