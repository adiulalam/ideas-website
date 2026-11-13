<?php

declare(strict_types=1);

namespace App\Support;

/** One-time messages shown on the next page (replaces the old generateAlert()). */
final class Flash
{
    private const SESSION_KEY = '_flash';

    public function success(string $message): void
    {
        $this->add('success', $message);
    }

    public function error(string $message): void
    {
        $this->add('error', $message);
    }

    public function add(string $type, string $message): void
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        if (!is_array($messages)) {
            $messages = [];
        }
        $messages[] = ['type' => $type, 'message' => $message];
        $_SESSION[self::SESSION_KEY] = $messages;
    }

    /** @return list<array{type: string, message: string}> */
    public function pull(): array
    {
        $messages = $_SESSION[self::SESSION_KEY] ?? [];
        unset($_SESSION[self::SESSION_KEY]);

        $valid = [];
        foreach (is_array($messages) ? $messages : [] as $message) {
            if (is_array($message) && is_string($message['type'] ?? null) && is_string($message['message'] ?? null)) {
                $valid[] = ['type' => $message['type'], 'message' => $message['message']];
            }
        }

        return $valid;
    }
}
