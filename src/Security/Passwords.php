<?php

declare(strict_types=1);

namespace App\Security;

/**
 * Password hashing.
 *
 * Historically passwords were stored as md5($password . 'ijdb'): unsalted per
 * user and trivially crackable. New hashes use password_hash(). Legacy hashes
 * are still accepted and transparently upgraded on the next successful login.
 */
final class Passwords
{
    public const MIN_LENGTH = 8;
    /** bcrypt silently ignores everything after 72 bytes, so refuse longer input. */
    public const MAX_BYTES = 72;

    private const LEGACY_SALT = 'ijdb';
    /** Hash of a throwaway value, used to keep timing equal for unknown emails. */
    private const DUMMY_HASH = '$2y$12$E.J/fAxG4zGF6MV0YuRWI.7JeO4JGR15Xpmhh1qaifZ9C1lRJLNtq';

    public static function hash(string $plain): string
    {
        return password_hash($plain, PASSWORD_DEFAULT);
    }

    public static function verify(string $plain, string $stored): bool
    {
        if ($stored === '') {
            return false;
        }
        if (self::isLegacy($stored)) {
            return hash_equals(strtolower($stored), md5($plain . self::LEGACY_SALT));
        }

        return password_verify($plain, $stored);
    }

    public static function isLegacy(string $stored): bool
    {
        return preg_match('/^[a-f0-9]{32}$/i', $stored) === 1;
    }

    public static function needsRehash(string $stored): bool
    {
        return self::isLegacy($stored) || password_needs_rehash($stored, PASSWORD_DEFAULT);
    }

    /** Spend the same time as a real verification so login timing does not reveal which emails exist. */
    public static function burnTime(string $plain): void
    {
        password_verify($plain, self::DUMMY_HASH);
    }

    /** @return string|null An error message, or null when the password is acceptable. */
    public static function validate(string $password, string $confirmation): ?string
    {
        if (mb_strlen($password, 'UTF-8') < self::MIN_LENGTH) {
            return sprintf('Passwords must be at least %d characters.', self::MIN_LENGTH);
        }
        if (strlen($password) > self::MAX_BYTES) {
            return sprintf('Passwords can be at most %d characters.', self::MAX_BYTES);
        }
        if (!hash_equals($password, $confirmation)) {
            return 'The passwords do not match.';
        }

        return null;
    }
}
