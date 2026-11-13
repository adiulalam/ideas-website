<?php
/**
 * @var App\View $view
 * @var App\Security\Csrf $csrf
 * @var string $title
 * @var array{ID: int, Name: string, Image: string}|null $category
 * @var string $name
 * @var array<string, string> $errors
 */
$isNew = $category === null;
?>
<section class="mx-auto max-w-xl px-4 py-8">
    <div class="card p-6 sm:p-8">
        <h1 class="mb-6 text-2xl font-bold text-white"><?= e($title) ?></h1>

        <form method="post" action="<?= $isNew ? '/admin/categories/new' : '/admin/categories/edit' ?>" class="space-y-5">
            <?= $csrf->field() ?>
            <?php if (!$isNew): ?>
                <input type="hidden" name="id" value="<?= $category['ID'] ?>">
            <?php endif; ?>

            <div>
                <label for="name" class="label">Name</label>
                <input id="name" type="text" name="name" value="<?= e($name) ?>" required maxlength="100" class="input" placeholder="Category name">
                <?= $view->partial('partials/form_error', ['errors' => $errors, 'field' => 'name']) ?>
            </div>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="/admin/categories" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $isNew ? 'Add category' : 'Save changes' ?></button>
            </div>
        </form>
    </div>
</section>
