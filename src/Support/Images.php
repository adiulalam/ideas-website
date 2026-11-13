<?php

declare(strict_types=1);

namespace App\Support;

final class Images
{
    public const MAX_URL_LENGTH = 255;

    /**
     * Turn a stored image value (an absolute URL or a bare filename under
     * /assets/img/) into a safe `src`. Always escape the result with e().
     *
     * The old template echoed the raw value into the attribute and only
     * checked str_contains($image, 'https://'), so a value such as
     * `x" onerror="alert(1)" a="https://` executed script for every visitor.
     */
    public static function src(?string $stored): ?string
    {
        $stored = trim((string) $stored);
        if ($stored === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $stored) === 1) {
            return filter_var($stored, FILTER_VALIDATE_URL) !== false ? $stored : null;
        }

        $name = basename(str_replace('\\', '/', $stored));
        if ($name === '' || $name === '.' || $name === '..') {
            return null;
        }

        return '/assets/img/' . rawurlencode($name);
    }

    /** Validation for user-entered image links. */
    public static function isValidRemoteUrl(string $url): bool
    {
        if ($url === '' || strlen($url) > self::MAX_URL_LENGTH) {
            return false;
        }
        if (filter_var($url, FILTER_VALIDATE_URL) === false) {
            return false;
        }

        return strtolower((string) parse_url($url, PHP_URL_SCHEME)) === 'https'
            && (string) parse_url($url, PHP_URL_HOST) !== '';
    }
}
