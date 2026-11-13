<?php

declare(strict_types=1);

/*
 * Small global helpers for templates. Keep this file tiny: real logic lives
 * in App\Support so it can be unit tested.
 */

if (!function_exists('e')) {
    /** Escape a value for HTML text or a quoted attribute. Use for ALL dynamic output. */
    function e(string|int|float|bool|null|Stringable $value): string
    {
        if ($value === null) {
            return '';
        }
        if (is_bool($value)) {
            return $value ? '1' : '';
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
    }
}
