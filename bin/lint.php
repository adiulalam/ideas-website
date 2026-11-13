<?php

declare(strict_types=1);

/*
 * Syntax-check every first-party PHP file with `php -l`.
 *
 *   composer lint
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

$root = dirname(__DIR__);
$failed = 0;
$checked = 0;

foreach (['bin', 'database', 'public', 'src', 'templates', 'tests'] as $dir) {
    if (!is_dir($root . '/' . $dir)) {
        continue;
    }
    $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root . '/' . $dir, FilesystemIterator::SKIP_DOTS));
    foreach ($files as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        $checked++;
        exec(escapeshellarg(PHP_BINARY) . ' -l ' . escapeshellarg($file->getPathname()) . ' 2>&1', $output, $code);
        if ($code !== 0) {
            $failed++;
            fwrite(STDERR, implode("\n", $output) . "\n");
        }
        $output = [];
    }
}

echo "Checked {$checked} files, {$failed} with errors.\n";
exit($failed === 0 ? 0 : 1);
