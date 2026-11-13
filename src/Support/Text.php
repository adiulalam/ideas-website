<?php

declare(strict_types=1);

namespace App\Support;

final class Text
{
    public static function length(string $value): int
    {
        return mb_strlen($value, 'UTF-8');
    }

    /** Collapse control characters (including CR/LF) — for names, subjects and emails. */
    public static function singleLine(string $value): string
    {
        return trim((string) preg_replace('/[\x00-\x1F\x7F]+/u', ' ', $value));
    }

    /** Normalise newlines and drop other control characters — for comments and messages. */
    public static function multiLine(string $value): string
    {
        $value = str_replace(["\r\n", "\r"], "\n", $value);

        return trim((string) preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]+/u', '', $value));
    }
}
