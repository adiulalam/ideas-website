<?php

declare(strict_types=1);

namespace Tests;

use App\Services\HtmlSanitizer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class HtmlSanitizerTest extends TestCase
{
    private static HtmlSanitizer $sanitizer;

    public static function setUpBeforeClass(): void
    {
        self::$sanitizer = new HtmlSanitizer(sys_get_temp_dir() . '/htmlpurifier-tests');
    }

    #[DataProvider('xssPayloads')]
    public function testStripsScriptVectors(string $payload): void
    {
        foreach ([self::$sanitizer->clean($payload), self::$sanitizer->render($payload)] as $html) {
            self::assertStringNotContainsStringIgnoringCase('<script', $html);
            self::assertStringNotContainsStringIgnoringCase('onerror', $html);
            self::assertStringNotContainsStringIgnoringCase('onload', $html);
            self::assertStringNotContainsStringIgnoringCase('javascript:', $html);
            self::assertStringNotContainsStringIgnoringCase('<iframe', $html);
        }
    }

    /** @return iterable<array{string}> */
    public static function xssPayloads(): iterable
    {
        yield ['<script>alert(1)</script>Idea'];
        yield ['<img src=x onerror=alert(1)>'];
        yield ['<a href="javascript:alert(1)">click</a>'];
        yield ['<svg onload=alert(1)>'];
        yield ['<iframe src="https://evil.example"></iframe>'];
        yield ['<p style="background:url(javascript:alert(1))">x</p>'];
    }

    public function testKeepsLinksAndBasicFormatting(): void
    {
        $html = self::$sanitizer->clean('<b>Bold</b> and <a href="https://example.com">a link</a>');

        self::assertStringContainsString('<b>Bold</b>', $html);
        self::assertStringContainsString('href="https://example.com"', $html);
        self::assertStringContainsString('noopener', $html);
        self::assertStringContainsString('nofollow', $html);
    }

    public function testRenderLinkifiesButCleanDoesNot(): void
    {
        $text = 'See https://example.com/recipe for details';

        self::assertStringNotContainsString('<a ', self::$sanitizer->clean($text));
        self::assertStringContainsString('<a href="https://example.com/recipe"', self::$sanitizer->render($text));
    }

    public function testPlainText(): void
    {
        self::assertSame('Tom & Jerry say hi', self::$sanitizer->toPlainText('<b>Tom</b> &amp; Jerry <script>x</script> say   hi'));
        self::assertSame('', self::$sanitizer->toPlainText('<script>alert(1)</script>'));
    }
}
