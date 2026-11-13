<?php

declare(strict_types=1);

namespace App;

use InvalidArgumentException;
use RuntimeException;
use Throwable;

/**
 * Renders plain-PHP templates from /templates.
 *
 * Templates must escape every dynamic value with e(). Each template runs in
 * an isolated scope with only the variables passed in plus shared ones
 * (`view`, `auth`, `csrf`, `captcha`, `currentPath`, ...).
 */
final class View
{
    /** @var array<string, mixed> */
    private array $shared = [];

    public function __construct(private readonly string $templateDir)
    {
    }

    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    /** @param array<string, mixed> $data */
    public function render(string $template, array $data = [], ?string $layout = 'layout'): string
    {
        $content = $this->partial($template, $data);
        if ($layout === null) {
            return $content;
        }

        return $this->partial($layout, ['content' => $content] + $data);
    }

    /** @param array<string, mixed> $data */
    public function partial(string $template, array $data = []): string
    {
        if (preg_match('#^[a-z0-9_]+(?:/[a-z0-9_]+)*$#', $template) !== 1) {
            throw new InvalidArgumentException('Invalid template name: ' . $template);
        }

        $file = $this->templateDir . '/' . $template . '.php';
        if (!is_file($file)) {
            throw new RuntimeException('Template not found: ' . $template);
        }

        $vars = $data + $this->shared + ['view' => $this];

        ob_start();
        try {
            (static function (string $__file, array $__vars): void {
                extract($__vars, EXTR_SKIP);
                require $__file;
            })($file, $vars);
        } catch (Throwable $e) {
            ob_end_clean();
            throw $e;
        }

        return (string) ob_get_clean();
    }
}
