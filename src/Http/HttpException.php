<?php

declare(strict_types=1);

namespace App\Http;

use RuntimeException;

/** An error that should be shown to the visitor with the given status code. */
final class HttpException extends RuntimeException
{
    /** @param array<string, string> $headers */
    public function __construct(
        public readonly int $status,
        string $message = '',
        public readonly array $headers = [],
    ) {
        parent::__construct($message !== '' ? $message : self::defaultMessage($status), $status);
    }

    public static function title(int $status): string
    {
        return match ($status) {
            400 => 'Bad request',
            401 => 'Please log in',
            403 => 'Not allowed',
            404 => 'Page not found',
            405 => 'Method not allowed',
            413 => 'Upload too large',
            503 => 'Temporarily unavailable',
            default => $status >= 500 ? 'Something went wrong' : 'Error',
        };
    }

    public static function defaultMessage(int $status): string
    {
        return match ($status) {
            400 => 'The request could not be understood.',
            401 => 'You need to be logged in to do that.',
            403 => 'You do not have permission to do that.',
            404 => 'We could not find the page you were looking for.',
            405 => 'That action is not supported here.',
            413 => 'The upload was too large. Images can be at most 5 MB.',
            503 => 'The site is having trouble reaching its database. Please try again in a few minutes.',
            default => 'An unexpected error occurred. Please try again later.',
        };
    }
}
