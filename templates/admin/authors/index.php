<?php
/**
 * @var App\Security\Csrf $csrf
 * @var list<array<string, mixed>> $authors
 * @var array<string, mixed>|null $currentUser
 */
use App\Security\Roles;
use App\Support\Images;

$viewerIsSiteAdmin = in_array(Roles::SITE_ADMIN, $currentUser['roles'] ?? [], true);
?>
<section class="mx-auto max-w-6xl px-4 py-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-white">Manage authors</h1>
        <a href="/admin/authors/new" class="btn btn-primary">Add author</a>
    </div>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($authors as $author): ?>
            <?php
            $image = Images::src($author['Author_Image'] ?? null);
            $isSiteAdmin = in_array(Roles::SITE_ADMIN, $author['roles'], true);
            $editable = $viewerIsSiteAdmin || !$isSiteAdmin;
            $isSelf = (int) $author['Author_ID'] === (int) ($currentUser['Author_ID'] ?? 0);
            ?>
            <article class="card flex flex-col overflow-hidden">
                <?php if ($image !== null): ?>
                    <img src="<?= e($image) ?>" alt="" loading="lazy" class="aspect-video w-full bg-gray-900 object-cover" referrerpolicy="no-referrer">
                <?php endif; ?>
                <div class="flex flex-1 flex-col gap-2 p-4">
                    <h2 class="text-xl font-bold break-words text-white"><?= e($author['Name']) ?><?= $isSelf ? ' <span class="text-sm font-normal text-gray-400">(you)</span>' : '' ?></h2>
                    <p class="text-sm break-all text-gray-400"><?= e($author['Email']) ?></p>
                    <ul class="flex flex-wrap gap-1">
                        <?php foreach ($author['roles'] as $role): ?>
                            <li class="rounded-full bg-gray-700 px-2 py-0.5 text-xs text-gray-200"><?= e($role) ?></li>
                        <?php endforeach; ?>
                    </ul>
                    <?php if ($editable): ?>
                        <div class="mt-auto flex justify-end gap-2 pt-3">
                            <a href="/admin/authors/edit?id=<?= (int) $author['Author_ID'] ?>" class="btn btn-primary btn-sm">Edit</a>
                            <?php if (!$isSelf): ?>
                                <form method="post" action="/admin/authors/delete" data-confirm="Delete <?= e($author['Name']) ?>? All of their ideas, comments and votes will be deleted too.">
                                    <?= $csrf->field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $author['Author_ID'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
