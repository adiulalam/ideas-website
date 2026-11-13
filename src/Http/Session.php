<?php

declare(strict_types=1);

namespace App\Http;

final class Session
{
    public const COOKIE_NAME = 'ideas_session';

    /**
     * Start the session with hardened cookie settings. The old code used
     * PHP defaults: no HttpOnly (so XSS could steal sessions), no SameSite
     * and no strict mode (session fixation).
     */
    public static function start(bool $secure): void
    {
        if (session_status() === PHP_SESSION_ACTIVE || PHP_SAPI === 'cli') {
            return;
        }

        ini_set('session.use_strict_mode', '1');
        ini_set('session.use_only_cookies', '1');
        ini_set('session.use_trans_sid', '0');

        session_name(self::COOKIE_NAME);
        session_set_cookie_params([
            'lifetime' => 0,
            'path' => '/',
            'secure' => $secure,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
        session_start();
    }
}
