<?php

declare(strict_types=1);

namespace App\Security;

/**
 * Synchroniser-token CSRF protection. Every POST is checked centrally in
 * App::handle(); templates add the hidden field with `$csrf->field()`.
 */
final class Csrf
{
    public const FIELD = '_token';
    private const SESSION_KEY = '_csrf';

    public function token(): string
    {
        $token = $_SESSION[self::SESSION_KEY] ?? null;
        if (!is_string($token) || strlen($token) !== 64) {
            $token = bin2hex(random_bytes(32));
            $_SESSION[self::SESSION_KEY] = $token;
        }

        return $token;
    }

    public function validate(string $submitted): bool
    {
        return $submitted !== '' && hash_equals($this->token(), $submitted);
    }

    public function field(): string
    {
        return '<input type="hidden" name="' . self::FIELD . '" value="' . e($this->token()) . '">';
    }

    public function regenerate(): void
    {
        unset($_SESSION[self::SESSION_KEY]);
    }
}
