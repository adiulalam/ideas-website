<?php

declare(strict_types=1);

namespace App\Security;

/**
 * Security headers for every dynamic response.
 *
 * The Content-Security-Policy only allows scripts from this site and Google
 * reCAPTCHA (plus the Cloudflare analytics beacon), so injected inline
 * scripts cannot run even if an escaping bug slips through.
 */
final class SecurityHeaders
{
    /** @return array<string, string> */
    public static function for(bool $https): array
    {
        $csp = [
            "default-src 'self'",
            "script-src 'self' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/ https://static.cloudflareinsights.com",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data: https: http:",
            "font-src 'self'",
            "connect-src 'self' https://www.google.com/recaptcha/ https://cloudflareinsights.com",
            'frame-src https://www.google.com/recaptcha/ https://recaptcha.google.com/recaptcha/',
            "form-action 'self'",
            "base-uri 'self'",
            "frame-ancestors 'none'",
            "object-src 'none'",
        ];
        if ($https) {
            $csp[] = 'upgrade-insecure-requests';
        }

        $headers = [
            'Content-Security-Policy' => implode('; ', $csp),
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'DENY',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Permissions-Policy' => 'camera=(), microphone=(), geolocation=(), payment=()',
            'Cache-Control' => 'no-store, private',
        ];
        if ($https) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000';
        }

        return $headers;
    }

    public static function send(bool $https): void
    {
        if (headers_sent()) {
            return;
        }
        header_remove('X-Powered-By');
        foreach (self::for($https) as $name => $value) {
            header($name . ': ' . $value);
        }
    }
}
