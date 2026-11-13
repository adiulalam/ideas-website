<?php

declare(strict_types=1);

namespace App\Security;

use App\Repository\AuthorRepository;
use PDOException;

/**
 * Session-based authentication and role checks.
 *
 * The session only stores the author ID. The user and their roles are loaded
 * from the database on each request, so role changes and account deletion
 * take effect immediately. (Previously the email and password hash were kept
 * in the session and roles were cached there.)
 */
final class Auth
{
    private const SESSION_KEY = 'user_id';

    /** @var array<string, mixed>|null */
    private ?array $user = null;
    private bool $resolved = false;

    public function __construct(private readonly AuthorRepository $authors)
    {
    }

    /** @return array<string, mixed>|null Author row plus a `roles` list. */
    public function user(): ?array
    {
        if (!$this->resolved) {
            $this->resolved = true;
            $id = $_SESSION[self::SESSION_KEY] ?? null;
            if (is_int($id)) {
                $this->user = $this->authors->findWithRoles($id);
                if ($this->user === null) {
                    unset($_SESSION[self::SESSION_KEY]);
                }
            }
        }

        return $this->user;
    }

    public function id(): ?int
    {
        $user = $this->user();

        return $user === null ? null : (int) $user['Author_ID'];
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function hasAnyRole(string ...$roles): bool
    {
        $user = $this->user();
        if ($user === null || !is_array($user['roles'] ?? null)) {
            return false;
        }

        return array_intersect($roles, $user['roles']) !== [];
    }

    public function canManageAuthors(): bool
    {
        return $this->hasAnyRole(Roles::SITE_ADMIN, Roles::ACCOUNT_ADMIN);
    }

    public function canManageCategories(): bool
    {
        return $this->hasAnyRole(Roles::SITE_ADMIN, Roles::CATEGORY_MODERATOR);
    }

    public function canModerateIdeas(): bool
    {
        return $this->hasAnyRole(Roles::SITE_ADMIN, Roles::CONTENT_EDITOR);
    }

    /** @param array<string, mixed> $idea */
    public function canEditIdea(array $idea): bool
    {
        $id = $this->id();

        return $id !== null && ((int) $idea['AuthorID'] === $id || $this->canModerateIdeas());
    }

    /**
     * Check credentials. On success, upgrades legacy/outdated hashes.
     *
     * @return array<string, mixed>|null The author row, or null on failure.
     */
    public function attempt(string $email, string $password): ?array
    {
        $author = $this->authors->findByEmail($email);
        $stored = $author['Password'] ?? null;
        if ($author === null || !is_string($stored) || $stored === '') {
            Passwords::burnTime($password);

            return null;
        }
        if (!Passwords::verify($password, $stored)) {
            return null;
        }

        if (Passwords::needsRehash($stored)) {
            try {
                if ($this->authors->passwordColumnFits()) {
                    $this->authors->updatePasswordHash((int) $author['Author_ID'], Passwords::hash($password));
                }
            } catch (PDOException $e) {
                error_log('[auth] could not upgrade password hash: ' . $e->getMessage());
            }
        }

        return $author;
    }

    /** @param array<string, mixed> $author */
    public function login(array $author): void
    {
        // New session ID on privilege change prevents session fixation.
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $_SESSION[self::SESSION_KEY] = (int) $author['Author_ID'];
        $this->resolved = false;
        $this->user = null;
    }

    public function logout(): void
    {
        $_SESSION = [];
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
        $this->resolved = true;
        $this->user = null;
    }
}
