<?php

declare(strict_types=1);

namespace App\Services;

use App\Config;

/** Google reCAPTCHA v2 ("I'm not a robot") verification. */
final class Captcha
{
    private const VERIFY_URL = 'https://www.google.com/recaptcha/api/siteverify';

    public function __construct(private readonly Config $config)
    {
    }

    public function enabled(): bool
    {
        return $this->config->captchaEnabled();
    }

    public function siteKey(): string
    {
        return $this->config->captchaSiteKey;
    }

    public function verify(string $response): bool
    {
        if (!$this->enabled()) {
            return true;
        }
        if ($this->config->captchaSecretKey === '') {
            error_log('[captcha] CAPTCHA_SECRET_KEY is not configured; rejecting submission.');

            return false;
        }
        if ($response === '') {
            return false;
        }

        // POST (not GET) so the secret never ends up in URLs or proxy logs,
        // and with a short timeout so Google being slow cannot hang the site.
        $context = stream_context_create([
            'http' => [
                'method' => 'POST',
                'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => http_build_query([
                    'secret' => $this->config->captchaSecretKey,
                    'response' => $response,
                ]),
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents(self::VERIFY_URL, false, $context);
        if ($body === false) {
            error_log('[captcha] verification request failed');

            return false;
        }

        $data = json_decode($body, true);

        return is_array($data) && ($data['success'] ?? false) === true;
    }
}
