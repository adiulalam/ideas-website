<?php

declare(strict_types=1);

namespace App\Security;

/** Role IDs as stored in the `Role` table. */
final class Roles
{
    public const USER = 'User';
    public const CONTENT_EDITOR = 'Content Editor';
    public const SITE_ADMIN = 'Site Administrator';
    public const ACCOUNT_ADMIN = 'Account Administrator';
    public const CATEGORY_MODERATOR = 'Category Moderator';
}
