<?php
/**
 * @var App\Security\Csrf $csrf
 * @var string $currentPath
 * @var array<string, mixed>|null $currentUser
 */
use App\Security\Roles;

$roles = is_array($currentUser['roles'] ?? null) ? $currentUser['roles'] : [];
$canManageAuthors = array_intersect([Roles::SITE_ADMIN, Roles::ACCOUNT_ADMIN], $roles) !== [];
$canManageCategories = array_intersect([Roles::SITE_ADMIN, Roles::CATEGORY_MODERATOR], $roles) !== [];

$link = static function (string $href, string $label) use ($currentPath): string {
    $active = $currentPath === $href;

    return sprintf(
        '<a href="%s" class="nav-link%s"%s>%s</a>',
        e($href),
        $active ? ' nav-link-active' : '',
        $active ? ' aria-current="page"' : '',
        e($label),
    );
};
?>
<header class="border-b border-gray-700 bg-gray-800">
    <nav class="mx-auto flex max-w-6xl flex-wrap items-center justify-between gap-2 px-4 py-2" aria-label="Main">
        <a href="/" class="flex items-center gap-2 py-1 text-lg font-bold tracking-tight text-white">
            <svg class="h-6 w-6 text-yellow-300" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 18h6M10 22h4M12 2a7 7 0 0 0-4 12.74V16a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-1.26A7 7 0 0 0 12 2Z"/></svg>
            Ideal Ideas
        </a>

        <button type="button" data-nav-toggle aria-controls="main-menu" aria-expanded="false"
                class="inline-flex items-center rounded-lg p-2 text-gray-400 hover:bg-gray-700 focus:ring-2 focus:ring-gray-600 focus:outline-none md:hidden">
            <span class="sr-only">Open main menu</span>
            <svg class="h-6 w-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path fill-rule="evenodd" d="M3 5a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 10a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM3 15a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"/></svg>
        </button>

        <div id="main-menu" class="hidden w-full md:flex md:w-auto md:items-center md:gap-4">
            <ul class="flex flex-col gap-1 py-2 md:flex-row md:py-0">
                <li><?= $link('/', 'Home') ?></li>
                <li><?= $link('/contact', 'Contact me') ?></li>
                <li><a href="https://adiulalamadil.me/" class="nav-link" rel="noopener">About me</a></li>
                <?php if ($canManageAuthors): ?>
                    <li><?= $link('/admin/authors', 'Manage authors') ?></li>
                <?php endif; ?>
                <?php if ($canManageCategories): ?>
                    <li><?= $link('/admin/categories', 'Manage categories') ?></li>
                <?php endif; ?>
            </ul>

            <div class="flex flex-col gap-1 border-t border-gray-700 py-2 md:flex-row md:items-center md:border-0 md:py-0">
                <?php if ($currentUser !== null): ?>
                    <?= $link('/?author=' . (int) $currentUser['Author_ID'], 'My ideas') ?>
                    <?= $link('/ideas/new', 'New idea') ?>
                    <form method="post" action="/logout">
                        <?= $csrf->field() ?>
                        <button type="submit" class="nav-link w-full text-left">Log out <span class="sr-only">(<?= e($currentUser['Name']) ?>)</span></button>
                    </form>
                <?php else: ?>
                    <?= $link('/register', 'Register') ?>
                    <?= $link('/login', 'Log in') ?>
                <?php endif; ?>
            </div>
        </div>
    </nav>
</header>
