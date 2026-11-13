<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Typed, defensive access to request data.
 *
 * Every accessor coerces to the expected type so that crafted input such as
 * `?page[]=1` or `text[]=x` can never reach SQL or string functions as an
 * array (the old code crashed or misbehaved on those).
 */
final class Request
{
    /**
     * @param array<array-key, mixed> $query
     * @param array<array-key, mixed> $post
     * @param array<array-key, mixed> $files
     * @param array<array-key, mixed> $server
     */
    public function __construct(
        private readonly array $query = [],
        private readonly array $post = [],
        private readonly array $files = [],
        private readonly array $server = [],
    ) {
    }

    public static function fromGlobals(): self
    {
        return new self($_GET, $_POST, $_FILES, $_SERVER);
    }

    public function method(): string
    {
        $method = $this->server['REQUEST_METHOD'] ?? 'GET';

        return is_string($method) ? strtoupper($method) : 'GET';
    }

    public function path(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = is_string($uri) ? parse_url($uri, PHP_URL_PATH) : null;

        return is_string($path) && $path !== '' ? $path : '/';
    }

    /** Path plus query string, used to send users back after logging in. */
    public function pathWithQuery(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';

        return is_string($uri) && $uri !== '' ? $uri : '/';
    }

    /** @return array<array-key, mixed> */
    public function queryAll(): array
    {
        return $this->query;
    }

    public function query(string $key, string $default = ''): string
    {
        return self::stringValue($this->query[$key] ?? null) ?? $default;
    }

    public function input(string $key, string $default = ''): string
    {
        return self::stringValue($this->post[$key] ?? null) ?? $default;
    }

    public function queryInt(string $key): ?int
    {
        return self::intValue($this->query[$key] ?? null);
    }

    public function inputInt(string $key): ?int
    {
        return self::intValue($this->post[$key] ?? null);
    }

    public function hasInput(string $key): bool
    {
        return array_key_exists($key, $this->post);
    }

    /** @return list<int> */
    public function inputIntList(string $key): array
    {
        $values = $this->post[$key] ?? [];
        if (!is_array($values)) {
            return [];
        }

        $ids = [];
        foreach ($values as $value) {
            $id = self::intValue($value);
            if ($id !== null) {
                $ids[$id] = $id;
            }
        }

        return array_values($ids);
    }

    /** @return list<string> */
    public function inputStringList(string $key): array
    {
        $values = $this->post[$key] ?? [];
        if (!is_array($values)) {
            return [];
        }

        $strings = [];
        foreach ($values as $value) {
            $string = self::stringValue($value);
            if ($string !== null && $string !== '') {
                $strings[$string] = $string;
            }
        }

        return array_values($strings);
    }

    /**
     * A single uploaded file, or null when none was chosen.
     *
     * @return array<array-key, mixed>|null
     */
    public function file(string $key): ?array
    {
        $file = $this->files[$key] ?? null;
        if (!is_array($file) || !isset($file['error']) || !is_int($file['error'])) {
            return null;
        }
        if ($file['error'] === UPLOAD_ERR_NO_FILE) {
            return null;
        }

        return $file;
    }

    public function isHttps(): bool
    {
        $https = $this->server['HTTPS'] ?? '';
        if (is_string($https) && $https !== '' && strtolower($https) !== 'off') {
            return true;
        }

        // Production sits behind Cloudflare and a reverse proxy that terminate TLS.
        $proto = $this->server['HTTP_X_FORWARDED_PROTO'] ?? '';
        if (is_string($proto) && strtolower(trim(explode(',', $proto)[0])) === 'https') {
            return true;
        }

        $cfVisitor = $this->server['HTTP_CF_VISITOR'] ?? '';

        return is_string($cfVisitor) && str_contains($cfVisitor, '"https"');
    }

    /** True when PHP discarded the POST body because it exceeded post_max_size. */
    public function postBodyTooLarge(): bool
    {
        if ($this->method() !== 'POST') {
            return false;
        }
        $length = $this->server['CONTENT_LENGTH'] ?? 0;
        $limit = self::iniBytes((string) ini_get('post_max_size'));

        return $limit > 0 && is_numeric($length) && (int) $length > $limit;
    }

    public static function iniBytes(string $value): int
    {
        $value = trim($value);
        if ($value === '') {
            return 0;
        }
        $number = (int) $value;

        return match (strtolower(substr($value, -1))) {
            'g' => $number * 1024 ** 3,
            'm' => $number * 1024 ** 2,
            'k' => $number * 1024,
            default => $number,
        };
    }

    private static function stringValue(mixed $value): ?string
    {
        if (is_string($value)) {
            return str_replace("\0", '', $value);
        }
        if (is_int($value) || is_float($value)) {
            return (string) $value;
        }

        return null;
    }

    private static function intValue(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value >= 0 ? $value : null;
        }
        if (is_string($value) && preg_match('/^\s*\d{1,9}\s*$/', $value) === 1) {
            return (int) $value;
        }

        return null;
    }
}
