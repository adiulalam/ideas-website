<?php

declare(strict_types=1);

namespace App;

use PDO;
use PDOException;
use Throwable;

/**
 * Lazily-opened PDO connection with fail-fast behaviour.
 *
 * The old code relied on PHP's 60 second default socket timeout, so an
 * unreachable database made every page hang for a minute. We now give up
 * after a few seconds and remember the failure for the rest of the request.
 */
final class Database
{
    public const CONNECT_TIMEOUT_SECONDS = 5;

    private ?PDO $pdo = null;
    private ?DatabaseUnavailable $failure = null;

    public function __construct(private readonly Config $config)
    {
    }

    public function pdo(): PDO
    {
        if ($this->pdo !== null) {
            return $this->pdo;
        }
        if ($this->failure !== null) {
            throw $this->failure;
        }

        try {
            $this->pdo = self::connect($this->config);
        } catch (PDOException $e) {
            $this->failure = new DatabaseUnavailable(
                sprintf('Could not connect to MySQL at %s:%d: %s', $this->config->dbHost, $this->config->dbPort, $e->getMessage()),
                0,
                $e,
            );
            throw $this->failure;
        }

        return $this->pdo;
    }

    public static function connect(Config $config, int $timeoutSeconds = self::CONNECT_TIMEOUT_SECONDS): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $config->dbHost,
            $config->dbPort,
            $config->dbName,
        );

        return new PDO($dsn, $config->dbUser, $config->dbPassword, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_TIMEOUT => $timeoutSeconds,
        ]);
    }

    /**
     * Run $callback inside a transaction, rolling back on any exception.
     *
     * @template T
     * @param callable(PDO): T $callback
     * @return T
     */
    public function transaction(callable $callback): mixed
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();
        try {
            $result = $callback($pdo);
            $pdo->commit();

            return $result;
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
