<?php

declare(strict_types=1);

/*
 * Apply pending database migrations.
 *
 *   php bin/migrate.php
 *
 * Runs automatically when the Docker container starts (docker/entrypoint.sh).
 * Exits non-zero if the database is unreachable or a migration fails.
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
require $root . '/vendor/autoload.php';

$config = App\Config::load($root);

try {
    $pdo = App\Database::connect($config);
} catch (PDOException $e) {
    fwrite(STDERR, sprintf("[migrate] cannot connect to %s:%d: %s\n", $config->dbHost, $config->dbPort, $e->getMessage()));
    exit(1);
}

try {
    foreach ((new App\Migrations\Migrator($pdo, $root . '/database/migrations'))->run() as $line) {
        echo "[migrate] {$line}\n";
    }
} catch (Throwable $e) {
    fwrite(STDERR, '[migrate] failed: ' . $e->getMessage() . "\n");
    exit(1);
}
