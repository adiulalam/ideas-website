<?php

declare(strict_types=1);

namespace App;

/**
 * Application configuration.
 *
 * Values come from real environment variables first. For backwards
 * compatibility with the existing production server we also read the legacy
 * `environment.php` file in the project root, which assigns into `$_ENV`
 * (HOST, PORT, DATABASE, USERNAME, PASSWORD, EMAIL, EMAIL_PASSWORD,
 * CAPTCHA_SECRET_KEY). Legacy key names are only honoured from that file so
 * generic process variables such as HOST or USERNAME are never picked up.
 */
final class Config
{
    /** Public reCAPTCHA v2 site key the production site has always used. */
    public const DEFAULT_CAPTCHA_SITE_KEY = '6LfzRtIhAAAAAPlVMVWxaishNuL6inEyPsTIFSD6';

    public function __construct(
        public readonly string $rootDir,
        public readonly string $env = 'production',
        public readonly bool $debug = false,
        public readonly string $appUrl = '',
        public readonly string $dbHost = '127.0.0.1',
        public readonly int $dbPort = 3306,
        public readonly string $dbName = '',
        public readonly string $dbUser = '',
        public readonly string $dbPassword = '',
        public readonly bool $mailEnabled = false,
        public readonly string $mailHost = 'smtp.gmail.com',
        public readonly int $mailPort = 587,
        public readonly string $mailEncryption = 'tls',
        public readonly string $mailUsername = '',
        public readonly string $mailPassword = '',
        public readonly string $mailFromAddress = '',
        public readonly string $mailFromName = 'Ideal Ideas',
        public readonly string $contactTo = '',
        public readonly string $captchaSiteKey = self::DEFAULT_CAPTCHA_SITE_KEY,
        public readonly string $captchaSecretKey = '',
        public readonly string $uploadDir = '',
    ) {
    }

    public static function load(string $rootDir): self
    {
        $legacy = self::readLegacyFile($rootDir . '/environment.php');

        $get = static function (string $key, ?string $legacyKey = null) use ($legacy): ?string {
            $value = getenv($key);
            if (is_string($value) && $value !== '') {
                return $value;
            }
            foreach ([$key, $legacyKey] as $candidate) {
                if ($candidate !== null && isset($legacy[$candidate]) && $legacy[$candidate] !== '') {
                    return $legacy[$candidate];
                }
            }

            return null;
        };

        [$dbHost, $portInHost] = self::splitHostPort($get('DB_HOST', 'HOST') ?? '127.0.0.1');
        $mailUsername = $get('MAIL_USERNAME', 'EMAIL') ?? '';
        $mailFrom = $get('MAIL_FROM_ADDRESS') ?? $mailUsername;
        $mailHost = $get('MAIL_HOST') ?? 'smtp.gmail.com';

        return new self(
            rootDir: $rootDir,
            env: strtolower($get('APP_ENV') ?? 'production'),
            debug: self::toBool($get('APP_DEBUG'), false),
            appUrl: rtrim($get('APP_URL') ?? '', '/'),
            dbHost: $dbHost,
            dbPort: (int) ($get('DB_PORT', 'PORT') ?? $portInHost ?? 3306),
            dbName: $get('DB_DATABASE', 'DATABASE') ?? '',
            dbUser: $get('DB_USERNAME', 'USERNAME') ?? '',
            dbPassword: $get('DB_PASSWORD', 'PASSWORD') ?? '',
            mailEnabled: self::toBool($get('MAIL_ENABLED'), true) && $mailFrom !== '',
            mailHost: $mailHost,
            mailPort: (int) ($get('MAIL_PORT') ?? 587),
            mailEncryption: strtolower($get('MAIL_ENCRYPTION') ?? 'tls'),
            mailUsername: $mailUsername,
            mailPassword: $get('MAIL_PASSWORD', 'EMAIL_PASSWORD') ?? '',
            mailFromAddress: $mailFrom,
            mailFromName: $get('MAIL_FROM_NAME') ?? 'Adiul Alam Adil',
            contactTo: $get('CONTACT_TO') ?? $mailFrom,
            captchaSiteKey: $get('CAPTCHA_SITE_KEY') ?? self::DEFAULT_CAPTCHA_SITE_KEY,
            captchaSecretKey: $get('CAPTCHA_SECRET_KEY') ?? '',
            uploadDir: $get('UPLOAD_DIR') ?? $rootDir . '/public/assets/img',
        );
    }

    public function isProduction(): bool
    {
        return $this->env === 'production';
    }

    /**
     * reCAPTCHA is on whenever a secret is configured, and always in
     * production (where a missing secret makes verification fail closed).
     */
    public function captchaEnabled(): bool
    {
        return $this->captchaSecretKey !== '' || $this->isProduction();
    }

    /**
     * Split a legacy "host:port" value. The old DSN was built as
     * "host=$HOST:$PORT", which PHP's socket layer splits on the first colon.
     *
     * @return array{0: string, 1: int|null}
     */
    public static function splitHostPort(string $host): array
    {
        $host = trim($host);
        if (preg_match('/^([^:\[\]]+):(\d{1,5})?$/', $host, $m) === 1) {
            return [$m[1], isset($m[2]) ? (int) $m[2] : null];
        }

        return [$host, null];
    }

    private static function toBool(?string $value, bool $default): bool
    {
        if ($value === null) {
            return $default;
        }

        return in_array(strtolower($value), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * Execute the legacy environment file in isolation and return what it
     * assigned into $_ENV, leaving the real superglobal untouched.
     *
     * @return array<string, string>
     */
    private static function readLegacyFile(string $file): array
    {
        if (!is_file($file)) {
            return [];
        }

        $before = $_ENV;
        (static function (string $__file): void {
            require $__file;
        })($file);
        $loaded = $_ENV;
        $_ENV = $before;

        $values = [];
        foreach ($loaded as $key => $value) {
            if (is_string($key) && is_scalar($value)) {
                $values[$key] = trim((string) $value);
            }
        }

        return $values;
    }
}
