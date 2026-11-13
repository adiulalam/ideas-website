<?php

declare(strict_types=1);

namespace Tests;

use App\Security\Csrf;
use PHPUnit\Framework\TestCase;

final class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        $_SESSION = [];
    }

    public function testTokenIsStableWithinASession(): void
    {
        $csrf = new Csrf();
        $token = $csrf->token();

        self::assertSame(64, strlen($token));
        self::assertSame($token, $csrf->token());
        self::assertTrue($csrf->validate($token));
    }

    public function testRejectsMissingOrWrongTokens(): void
    {
        $csrf = new Csrf();
        $token = $csrf->token();

        self::assertFalse($csrf->validate(''));
        self::assertFalse($csrf->validate(str_repeat('0', 64)));
        self::assertFalse($csrf->validate(substr($token, 0, 63)));
    }

    public function testTokenFromAnotherSessionIsRejected(): void
    {
        $token = (new Csrf())->token();
        $_SESSION = [];

        self::assertFalse((new Csrf())->validate($token));
    }

    public function testRegenerateIssuesANewToken(): void
    {
        $csrf = new Csrf();
        $old = $csrf->token();
        $csrf->regenerate();

        self::assertNotSame($old, $csrf->token());
        self::assertFalse($csrf->validate($old));
    }

    public function testFieldIsEscapedHiddenInput(): void
    {
        $field = (new Csrf())->field();

        self::assertStringStartsWith('<input type="hidden" name="_token" value="', $field);
    }
}
