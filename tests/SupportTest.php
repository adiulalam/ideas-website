<?php

declare(strict_types=1);

namespace Tests;

use App\Support\Images;
use App\Support\Text;
use App\Support\Time;
use App\Support\Url;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class SupportTest extends TestCase
{
    public function testEscapeHelper(): void
    {
        self::assertSame('&lt;script&gt;alert(&quot;x&quot;)&lt;/script&gt;', e('<script>alert("x")</script>'));
        self::assertSame('&apos; onerror=&apos;x', e("' onerror='x"));
        self::assertSame('', e(null));
        self::assertSame('42', e(42));
    }

    #[DataProvider('unsafeRedirects')]
    public function testUnsafeRedirectsFallBack(string $target): void
    {
        self::assertSame('/fallback', Url::safeRedirect($target, '/fallback'));
    }

    /** @return iterable<array{string}> */
    public static function unsafeRedirects(): iterable
    {
        yield ['https://evil.example'];
        yield ['//evil.example'];
        yield ['/\\evil.example'];
        yield ['javascript:alert(1)'];
        yield ["/ok\r\nSet-Cookie: x=1"];
        yield ['relative/path'];
    }

    public function testSafeRedirectsPassThrough(): void
    {
        self::assertSame('/idea?id=3#comments', Url::safeRedirect('/idea?id=3#comments'));
        self::assertSame('/', Url::safeRedirect(null));
    }

    public function testUrlBuilderDropsEmptyValues(): void
    {
        self::assertSame('/?author=2&page=3', Url::to('/', ['author' => 2, 'q' => '', 'category' => null, 'page' => 3]));
        self::assertSame('/', Url::to('/', ['q' => '']));
    }

    /** The old template echoed these straight into src="...". */
    public function testImageSrcNeutralisesStoredXss(): void
    {
        $src = (string) Images::src('x" onerror="alert(1)" a="https://');
        self::assertStringStartsWith('/assets/img/', $src);
        self::assertStringNotContainsString('"', $src);
        self::assertStringNotContainsString(' ', $src);
        self::assertSame('/assets/img/javascript%3Aalert%281%29', Images::src('javascript:alert(1)'));
        self::assertSame('/assets/img/a%22%20onerror%3D%22alert%281%29.png', Images::src('a" onerror="alert(1).png'));
        self::assertSame('/assets/img/passwd', Images::src('../../etc/passwd'));
    }

    public function testImageSrcHandlesNormalValues(): void
    {
        self::assertNull(Images::src(''));
        self::assertNull(Images::src(null));
        self::assertSame('https://example.com/a.jpg', Images::src('https://example.com/a.jpg'));
        self::assertSame('/assets/img/sushi.jpg', Images::src('sushi.jpg'));
        self::assertSame('/assets/img/opinions%20.jpg', Images::src('opinions .jpg'));
        self::assertSame('/assets/img/fruits.png', Images::src('../../assets/img/fruits.png'));
    }

    public function testRemoteImageUrlValidation(): void
    {
        self::assertTrue(Images::isValidRemoteUrl('https://example.com/a.jpg'));
        self::assertFalse(Images::isValidRemoteUrl('http://example.com/a.jpg'));
        self::assertFalse(Images::isValidRemoteUrl('javascript:alert(1)//https://'));
        self::assertFalse(Images::isValidRemoteUrl('https://example.com/" onerror="x'));
        self::assertFalse(Images::isValidRemoteUrl('https://example.com/' . str_repeat('a', 300)));
    }

    public function testTimeAgo(): void
    {
        $now = strtotime('2026-11-12 12:00:00');

        self::assertSame('just now', Time::ago('2026-11-12 11:59:30', $now));
        self::assertSame('1 minute ago', Time::ago('2026-11-12 11:59:00', $now));
        self::assertSame('5 hours ago', Time::ago('2026-11-12 07:00:00', $now));
        self::assertSame('2 days ago', Time::ago('2026-11-10 12:00:00', $now));
        self::assertSame('just now', Time::ago('2026-11-13 12:00:00', $now));
        self::assertSame('', Time::ago('not a date', $now));
    }

    public function testTextNormalisation(): void
    {
        self::assertSame('Subject Bcc: x@example.com', Text::singleLine("Subject\r\nBcc: x@example.com"));
        self::assertSame("line one\nline two", Text::multiLine("  line one\r\nline two\0 "));
        self::assertSame(3, Text::length('héé'));
    }
}
