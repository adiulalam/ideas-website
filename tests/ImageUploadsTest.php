<?php

declare(strict_types=1);

namespace Tests;

use App\Services\ImageUploads;
use App\Services\UploadException;
use PHPUnit\Framework\TestCase;

final class ImageUploadsTest extends TestCase
{
    private string $dir;
    private ImageUploads $uploads;

    protected function setUp(): void
    {
        $this->dir = sys_get_temp_dir() . '/ideas-upload-test-' . bin2hex(random_bytes(4));
        mkdir($this->dir);
        $this->uploads = new ImageUploads($this->dir, 1024 * 1024);
    }

    protected function tearDown(): void
    {
        foreach (glob($this->dir . '/{,.}*', GLOB_BRACE) ?: [] as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
        @rmdir($this->dir);
        @rmdir($this->dir . '-sibling');
    }

    public function testAcceptsARealPng(): void
    {
        $path = $this->dir . '/real.png';
        // 1x1 transparent PNG.
        file_put_contents($path, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=', true));

        self::assertSame('png', $this->uploads->validate($path));
    }

    public function testRejectsHtmlDisguisedAsAnImage(): void
    {
        $path = $this->dir . '/evil.png';
        file_put_contents($path, '<html><script>alert(1)</script></html>');

        $this->expectException(UploadException::class);
        $this->uploads->validate($path);
    }

    public function testRejectsPhpDisguisedAsAnImage(): void
    {
        $path = $this->dir . '/shell.php.jpg';
        file_put_contents($path, '<?php system($_GET["c"]); ?>');

        $this->expectException(UploadException::class);
        $this->uploads->validate($path);
    }

    public function testRejectsOversizedFiles(): void
    {
        $path = $this->dir . '/big.png';
        file_put_contents($path, str_repeat('a', 1024 * 1024 + 1));

        $this->expectException(UploadException::class);
        $this->uploads->validate($path);
    }

    public function testOnlyOurOwnFilesAreManaged(): void
    {
        self::assertTrue($this->uploads->isManaged('u_' . str_repeat('ab', 16) . '.png'));
        self::assertTrue($this->uploads->isManaged('2022-09-19_03.04.24_6571_Screenshot 2022-08-29 133005.png'));

        self::assertFalse($this->uploads->isManaged('sushi.jpg'));
        self::assertFalse($this->uploads->isManaged('../../environment.php'));
        self::assertFalse($this->uploads->isManaged('../../index.php'));
        self::assertFalse($this->uploads->isManaged('u_' . str_repeat('ab', 16) . '.php'));
        self::assertFalse($this->uploads->isManaged('https://example.com/u_' . str_repeat('ab', 16) . '.png'));
        self::assertFalse($this->uploads->isManaged(''));
        self::assertFalse($this->uploads->isManaged(null));
    }

    /** The old edit handler unlink()ed a path taken straight from the form. */
    public function testDeleteCannotEscapeTheUploadDirectory(): void
    {
        $outside = $this->dir . '-sibling';
        mkdir($outside);
        $victim = $outside . '/environment.php';
        file_put_contents($victim, '<?php // secret');

        $this->uploads->delete('../' . basename($outside) . '/environment.php');
        $this->uploads->delete('2022-01-01_00.00.00_1_../../' . basename($outside) . '/environment.php');

        self::assertFileExists($victim);
        unlink($victim);
    }

    public function testDeleteRemovesManagedFilesOnly(): void
    {
        $managed = 'u_' . str_repeat('cd', 16) . '.jpg';
        file_put_contents($this->dir . '/' . $managed, 'x');
        file_put_contents($this->dir . '/sushi.jpg', 'x');

        $this->uploads->delete($managed);
        $this->uploads->delete('sushi.jpg');

        self::assertFileDoesNotExist($this->dir . '/' . $managed);
        self::assertFileExists($this->dir . '/sushi.jpg');
    }
}
