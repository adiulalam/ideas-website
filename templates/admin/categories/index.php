<?php
/**
 * @var App\Security\Csrf $csrf
 * @var list<array{ID: int, Name: string, Image: string}> $categories
 */
use App\Support\Images;
?>
<section class="mx-auto max-w-6xl px-4 py-6">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <h1 class="text-2xl font-bold text-white">Manage categories</h1>
        <a href="/admin/categories/new" class="btn btn-primary">Add category</a>
    </div>

    <?php if ($categories === []): ?>
        <p class="card p-8 text-center text-gray-300">No categories yet.</p>
    <?php endif; ?>

    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
        <?php foreach ($categories as $category): ?>
            <?php $image = Images::src($category['Image']); ?>
            <article class="card flex flex-col overflow-hidden">
                <?php if ($image !== null): ?>
                    <img src="<?= e($image) ?>" alt="" loading="lazy" class="aspect-video w-full bg-gray-900 object-cover" referrerpolicy="no-referrer">
                <?php endif; ?>
                <div class="flex flex-1 items-center justify-between gap-3 p-4">
                    <h2 class="text-xl font-bold break-words text-white">
                        <a href="/?category=<?= $category['ID'] ?>" class="hover:underline"><?= e($category['Name']) ?></a>
                    </h2>
                    <div class="flex shrink-0 gap-2">
                        <a href="/admin/categories/edit?id=<?= $category['ID'] ?>" class="btn btn-primary btn-sm">Edit</a>
                        <form method="post" action="/admin/categories/delete" data-confirm="Delete the category “<?= e($category['Name']) ?>”? Ideas keep their other categories.">
                            <?= $csrf->field() ?>
                            <input type="hidden" name="id" value="<?= $category['ID'] ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>
