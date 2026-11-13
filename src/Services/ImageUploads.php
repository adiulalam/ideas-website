<?php

declare(strict_types=1);

namespace App\Services;

use finfo;
use RuntimeException;

/**
 * Stores user-uploaded idea images.
 *
 * Previously the original client filename was kept (allowing HTML injection
 * through the name), only the extension was checked, and the edit form told
 * the server which old file to unlink() — an unauthenticated arbitrary file
 * deletion. Now the content is verified, the server picks a random name, and
 * only files that match our own naming patterns can ever be deleted.
 */
final class ImageUploads
{
    public const MAX_BYTES = 5 * 1024 * 1024;

    /** @var array<string, string> MIME type => extension */
    private const TYPES = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    /** New uploads: u_<32 hex>.<ext> */
    private const MANAGED_PATTERN = '/^u_[a-f0-9]{32}\.(?:jpg|png|gif|webp)$/';
    /** Uploads made by the old code: YYYY-MM-DD_hh.mm.ss_<authorId>_<original name>.(jpg|png) */
    private const LEGACY_PATTERN = '/^\d{4}-\d{2}-\d{2}_\d{2}\.\d{2}\.\d{2}_\d+_[^\/\\\\]+\.(?:jpg|png)$/i';

    public function __construct(
        private readonly string $directory,
        private readonly int $maxBytes = self::MAX_BYTES,
    ) {
    }

    /**
     * Validate and move an uploaded file into place.
     *
     * @param array<array-key, mixed> $file One entry from $_FILES.
     * @return string The stored filename (no directory).
     */
    public function store(array $file): string
    {
        $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
        if ($error === UPLOAD_ERR_INI_SIZE || $error === UPLOAD_ERR_FORM_SIZE) {
            throw new UploadException('That image is too large. The maximum size is 5 MB.');
        }
        if ($error !== UPLOAD_ERR_OK) {
            throw new UploadException('The image upload failed. Please try again.');
        }

        $tmp = $file['tmp_name'] ?? null;
        if (!is_string($tmp) || !is_uploaded_file($tmp)) {
            throw new UploadException('The image upload failed. Please try again.');
        }

        $extension = $this->validate($tmp);
        $name = 'u_' . bin2hex(random_bytes(16)) . '.' . $extension;
        $target = $this->directory . DIRECTORY_SEPARATOR . $name;

        if (!is_dir($this->directory) || !move_uploaded_file($tmp, $target)) {
            throw new RuntimeException('Could not move uploaded image into ' . $this->directory);
        }
        @chmod($target, 0644);

        return $name;
    }

    /**
     * Check that a file on disk is a real, supported image.
     *
     * @return string The extension to store it under.
     */
    public function validate(string $path): string
    {
        $size = @filesize($path);
        if ($size === false || $size === 0) {
            throw new UploadException('The uploaded file is empty.');
        }
        if ($size > $this->maxBytes) {
            throw new UploadException('That image is too large. The maximum size is 5 MB.');
        }

        $mime = (new finfo(FILEINFO_MIME_TYPE))->file($path);
        if (!is_string($mime) || !isset(self::TYPES[$mime])) {
            throw new UploadException('Images must be JPG, PNG, GIF or WebP files.');
        }
        if (@getimagesize($path) === false) {
            throw new UploadException('That file does not look like a valid image.');
        }

        return self::TYPES[$mime];
    }

    /** Whether a stored Image value is a file we created (and may therefore delete). */
    public function isManaged(?string $filename): bool
    {
        if ($filename === null || $filename === '' || basename($filename) !== $filename) {
            return false;
        }

        return preg_match(self::MANAGED_PATTERN, $filename) === 1
            || preg_match(self::LEGACY_PATTERN, $filename) === 1;
    }

    /** Delete a previously uploaded image. Silently ignores anything we did not create. */
    public function delete(?string $filename): void
    {
        if (!$this->isManaged($filename)) {
            return;
        }

        $path = $this->directory . DIRECTORY_SEPARATOR . $filename;
        $real = realpath($path);
        $root = realpath($this->directory);
        if ($real !== false && $root !== false && dirname($real) === $root && is_file($real)) {
            @unlink($real);
        }
    }
}
