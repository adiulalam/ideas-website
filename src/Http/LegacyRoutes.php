<?php

declare(strict_types=1);

namespace App\Http;

/**
 * Permanent redirects from the old file-based URLs (e.g. links in old emails
 * or bookmarks) to the new routes.
 */
final class LegacyRoutes
{
    private const MAP = [
        '/views/auth/login' => '/login',
        '/views/auth/login/index.php' => '/login',
        '/views/auth/login/login.html.php' => '/login',
        '/views/auth/register' => '/register',
        '/views/auth/register/index.php' => '/register',
        '/views/auth/register/register.html.php' => '/register',
        '/views/contact' => '/contact',
        '/views/contact/index.php' => '/contact',
        '/views/contact/contact.html.php' => '/contact',
        '/views/admin/author' => '/admin/authors',
        '/views/admin/author/index.php' => '/admin/authors',
        '/views/admin/category' => '/admin/categories',
        '/views/admin/category/index.php' => '/admin/categories',
    ];

    /** @param array<array-key, mixed> $query */
    public static function redirectFor(string $method, string $path, array $query): ?string
    {
        if ($method !== 'GET' && $method !== 'HEAD') {
            return null;
        }

        $path = Router::normalize($path);
        if (isset(self::MAP[$path])) {
            return self::MAP[$path];
        }

        if ($path === '/') {
            $action = $query['action'] ?? null;
            $ideaId = $query['ideaID'] ?? null;
            if ($action === 'Comment' && is_string($ideaId) && ctype_digit($ideaId)) {
                return '/idea?id=' . (int) $ideaId;
            }
            if ($action === 'addContent') {
                return '/ideas/new';
            }
        }

        return null;
    }
}
