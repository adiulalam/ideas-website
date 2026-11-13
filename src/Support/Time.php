<?php

declare(strict_types=1);

namespace App\Support;

final class Time
{
    /** @var array<int, string> */
    private const UNITS = [
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
    ];

    public static function ago(string $datetime, ?int $now = null): string
    {
        $timestamp = strtotime($datetime);
        if ($timestamp === false) {
            return '';
        }

        $diff = max(0, ($now ?? time()) - $timestamp);
        foreach (self::UNITS as $seconds => $unit) {
            if ($diff >= $seconds) {
                $count = intdiv($diff, $seconds);

                return $count . ' ' . $unit . ($count === 1 ? '' : 's') . ' ago';
            }
        }

        return 'just now';
    }

    public static function date(string $date): string
    {
        $timestamp = strtotime($date);

        return $timestamp === false ? $date : date('j M Y', $timestamp);
    }
}
