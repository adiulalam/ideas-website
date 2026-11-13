<?php

declare(strict_types=1);

namespace Tests;

use App\Config;
use PHPUnit\Framework\TestCase;

/** Production still uses the legacy environment.php file; it must keep working. */
final class ConfigTest extends TestCase
{
    private string $dir;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/ideas-config-test-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
    }

    protected function tearDown(): void
    {
        @unlink($this->dir . '/environment.php');
        @rmdir($this->dir);
    }

    public function testLegacyEnvironmentFileIsMapped(): void
    {
        file_put_contents($this->dir . '/environment.php', <<<'PHP'
            <?php
            $_ENV["HOST"] = "10.1.2.3";
            $_ENV["PORT"] = "3307";
            $_ENV["DATABASE"] = "ideas_prod";
            $_ENV["USERNAME"] = "ideas_user";
            $_ENV["PASSWORD"] = "s3cret";
            $_ENV["EMAIL"] = "site@example.com";
            $_ENV["EMAIL_PASSWORD"] = "app-password";
            $_ENV["CAPTCHA_SECRET_KEY"] = "captcha-secret";
            PHP);
        $envBefore = $_ENV;

        $config = Config::load($this->dir);

        self::assertSame('10.1.2.3', $config->dbHost);
        self::assertSame(3307, $config->dbPort);
        self::assertSame('ideas_prod', $config->dbName);
        self::assertSame('ideas_user', $config->dbUser);
        self::assertSame('s3cret', $config->dbPassword);
        self::assertSame('site@example.com', $config->mailUsername);
        self::assertSame('site@example.com', $config->mailFromAddress);
        self::assertSame('site@example.com', $config->contactTo);
        self::assertSame('app-password', $config->mailPassword);
        self::assertSame('captcha-secret', $config->captchaSecretKey);
        self::assertSame(Config::DEFAULT_CAPTCHA_SITE_KEY, $config->captchaSiteKey);
        self::assertTrue($config->isProduction());
        self::assertTrue($config->mailEnabled);
        self::assertSame($envBefore, $_ENV, 'Secrets must not be left in $_ENV');
    }

    public function testLegacyHostWithEmbeddedPort(): void
    {
        file_put_contents($this->dir . '/environment.php', '<?php $_ENV["HOST"] = "db.example.com:3310"; $_ENV["PORT"] = "";');

        $config = Config::load($this->dir);

        self::assertSame('db.example.com', $config->dbHost);
        self::assertSame(3310, $config->dbPort);
    }

    public function testDefaultsWithoutAnyConfiguration(): void
    {
        $config = Config::load($this->dir);

        self::assertSame('production', $config->env);
        self::assertFalse($config->debug);
        self::assertFalse($config->mailEnabled);
        self::assertTrue($config->captchaEnabled());
        self::assertSame($this->dir . '/public/assets/img', $config->uploadDir);
    }
}
