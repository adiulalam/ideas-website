<?php

declare(strict_types=1);

namespace App\Http;

/** Minimal exact-match router: one handler per (method, path). */
final class Router
{
    /** @var array<string, array<string, callable(): Response>> */
    private array $routes = [];

    /** @param callable(): Response $handler */
    public function get(string $path, callable $handler): void
    {
        $this->add('GET', $path, $handler);
    }

    /** @param callable(): Response $handler */
    public function post(string $path, callable $handler): void
    {
        $this->add('POST', $path, $handler);
    }

    /** @param callable(): Response $handler */
    public function add(string $method, string $path, callable $handler): void
    {
        $this->routes[self::normalize($path)][strtoupper($method)] = $handler;
    }

    public function dispatch(string $method, string $path): Response
    {
        $path = self::normalize($path);
        $method = $method === 'HEAD' ? 'GET' : strtoupper($method);

        $handlers = $this->routes[$path] ?? null;
        if ($handlers === null) {
            throw new HttpException(404);
        }

        $handler = $handlers[$method] ?? null;
        if ($handler === null) {
            throw new HttpException(405, '', ['Allow' => implode(', ', array_keys($handlers))]);
        }

        return $handler();
    }

    public static function normalize(string $path): string
    {
        $path = '/' . trim($path, '/');

        return $path === '/index.php' ? '/' : $path;
    }
}
