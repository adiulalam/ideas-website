<?php

declare(strict_types=1);

namespace App\Support;

final class Url
{
    /**
     * Accept only same-site relative paths for redirects ("return_to",
     * post-login destination) so they cannot be abused as open redirects.
     */
    public static function safeRedirect(?string $target, string $fallback = '/'): string
    {
        if ($target === null || $target === '') {
            return $fallback;
        }
        if (!str_starts_with($target, '/') || str_starts_with($target, '//') || str_contains($target, '\\')) {
            return $fallback;
        }
        if (preg_match('/[\x00-\x1F\x7F]/', $target) === 1) {
            return $fallback;
        }

        return $target;
    }

    /** @param array<string, scalar|null> $query Empty values are dropped. */
    public static function to(string $path, array $query = []): string
    {
        $query = array_filter($query, static fn ($value): bool => $value !== null && $value !== '' && $value !== false);

        return $query === [] ? $path : $path . '?' . http_build_query($query);
    }
}
