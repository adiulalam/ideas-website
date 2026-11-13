<?php

declare(strict_types=1);

namespace Tests;

use App\Security\Passwords;
use PHPUnit\Framework\TestCase;

final class PasswordsTest extends TestCase
{
    public function testLegacyMd5HashesStillVerify(): void
    {
        $legacy = md5('correct horse' . 'ijdb');

        self::assertTrue(Passwords::isLegacy($legacy));
        self::assertTrue(Passwords::verify('correct horse', $legacy));
        self::assertTrue(Passwords::verify('correct horse', strtoupper($legacy)));
        self::assertFalse(Passwords::verify('wrong', $legacy));
        self::assertTrue(Passwords::needsRehash($legacy));
    }

    public function testModernHashes(): void
    {
        $hash = Passwords::hash('correct horse');

        self::assertFalse(Passwords::isLegacy($hash));
        self::assertTrue(Passwords::verify('correct horse', $hash));
        self::assertFalse(Passwords::verify('correct horsE', $hash));
        self::assertFalse(Passwords::needsRehash($hash));
    }

    public function testEmptyStoredHashNeverVerifies(): void
    {
        self::assertFalse(Passwords::verify('', ''));
        self::assertFalse(Passwords::verify('anything', ''));
    }

    public function testValidation(): void
    {
        self::assertNull(Passwords::validate('long enough', 'long enough'));
        self::assertNotNull(Passwords::validate('short', 'short'));
        self::assertNotNull(Passwords::validate('long enough', 'different!!'));
        self::assertNotNull(Passwords::validate(str_repeat('a', 73), str_repeat('a', 73)));
    }
}
