<?php
/**
 * @var App\View $view
 * @var App\Security\Csrf $csrf
 * @var string $title
 * @var array<string, mixed>|null $idea
 * @var array<string, mixed> $input
 * @var array<string, string> $errors
 * @var list<array{ID: int, Name: string, Image: string}> $categories
 * @var list<array{ID: int, Name: string}> $departments
 */
use App\Controllers\IdeaController;
use App\Services\ImageUploads;
use App\Support\Images;

$error = static fn (string $field): string => $view->partial('partials/form_error', ['errors' => $errors, 'field' => $field]);
$currentImage = $idea !== null ? Images::src($idea['Image']) : null;
?>
<section class="mx-auto max-w-2xl px-4 py-8">
    <div class="card p-6 sm:p-8">
        <h1 class="mb-6 text-2xl font-bold text-white"><?= e($title) ?></h1>

        <form method="post" action="<?= $idea === null ? '/ideas/new' : '/ideas/edit' ?>" enctype="multipart/form-data" class="space-y-6">
            <?= $csrf->field() ?>
            <?php if ($idea !== null): ?>
                <input type="hidden" name="id" value="<?= (int) $idea['ID'] ?>">
            <?php endif; ?>

            <div>
                <label for="text" class="label">Your idea</label>
                <textarea id="text" name="text" rows="4" required maxlength="<?= IdeaController::MAX_TEXT_LENGTH ?>" class="input" placeholder="Describe your idea"><?= e($input['text']) ?></textarea>
                <p class="mt-1 text-xs text-gray-400">Plain text. Links are allowed; other formatting is limited to bold, italic and underline.</p>
                <?= $error('text') ?>
            </div>

            <fieldset class="rounded-lg border border-gray-700 p-4">
                <legend class="px-1 text-sm font-medium text-white">Categories</legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    <?php foreach ($categories as $category): ?>
                        <label class="flex items-center gap-2 text-sm text-gray-300">
                            <input type="checkbox" name="categories[]" value="<?= $category['ID'] ?>" class="checkbox" <?= in_array($category['ID'], $input['categories'], true) ? 'checked' : '' ?>>
                            <?= e($category['Name']) ?>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?= $error('categories') ?>
            </fieldset>

            <?php if ($departments !== []): ?>
                <fieldset class="rounded-lg border border-gray-700 p-4">
                    <legend class="px-1 text-sm font-medium text-white">Departments</legend>
                    <div class="grid gap-2 sm:grid-cols-2">
                        <?php foreach ($departments as $department): ?>
                            <label class="flex items-center gap-2 text-sm text-gray-300">
                                <input type="checkbox" name="departments[]" value="<?= $department['ID'] ?>" class="checkbox" <?= in_array($department['ID'], $input['departments'], true) ? 'checked' : '' ?>>
                                <?= e($department['Name']) ?>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <?= $error('departments') ?>
                </fieldset>
            <?php endif; ?>

            <fieldset class="space-y-3 rounded-lg border border-gray-700 p-4">
                <legend class="px-1 text-sm font-medium text-white">Image (optional)</legend>
                <?php if ($currentImage !== null): ?>
                    <div>
                        <p class="mb-2 text-xs text-gray-400">Current image — upload or link a new one to replace it.</p>
                        <img src="<?= e($currentImage) ?>" alt="" class="max-h-40 rounded-lg" referrerpolicy="no-referrer">
                    </div>
                <?php endif; ?>
                <div>
                    <label for="image" class="label">Upload an image</label>
                    <input type="hidden" name="MAX_FILE_SIZE" value="<?= ImageUploads::MAX_BYTES ?>">
                    <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/gif,image/webp" class="input-file">
                    <p class="mt-1 text-xs text-gray-400">JPG, PNG, GIF or WebP, up to 5 MB.</p>
                </div>
                <div>
                    <label for="image_url" class="label">…or link to one</label>
                    <input id="image_url" name="image_url" type="url" value="<?= e($input['image_url']) ?>" maxlength="<?= Images::MAX_URL_LENGTH ?>" pattern="https://.*" placeholder="https://example.com/picture.jpg" class="input">
                </div>
                <?= $error('image') ?>
            </fieldset>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="<?= $idea === null ? '/' : '/idea?id=' . (int) $idea['ID'] ?>" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $idea === null ? 'Post idea' : 'Save changes' ?></button>
            </div>
        </form>
    </div>
</section>
