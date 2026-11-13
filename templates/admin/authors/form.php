<?php
/**
 * @var App\View $view
 * @var App\Security\Csrf $csrf
 * @var string $title
 * @var array<string, mixed>|null $author
 * @var array{name: string, email: string, roles: list<string>} $input
 * @var array<string, string> $errors
 * @var list<array{ID: string, Description: string}> $roles
 * @var bool $canGrantSiteAdmin
 */
use App\Security\Passwords;
use App\Security\Roles;

$error = static fn (string $field): string => $view->partial('partials/form_error', ['errors' => $errors, 'field' => $field]);
$isNew = $author === null;
?>
<section class="mx-auto max-w-xl px-4 py-8">
    <div class="card p-6 sm:p-8">
        <h1 class="mb-6 text-2xl font-bold text-white"><?= e($title) ?></h1>

        <form method="post" action="<?= $isNew ? '/admin/authors/new' : '/admin/authors/edit' ?>" class="space-y-5">
            <?= $csrf->field() ?>
            <?php if (!$isNew): ?>
                <input type="hidden" name="id" value="<?= (int) $author['Author_ID'] ?>">
            <?php endif; ?>

            <div>
                <label for="name" class="label">Name</label>
                <input id="name" type="text" name="name" value="<?= e($input['name']) ?>" required maxlength="100" class="input">
                <?= $error('name') ?>
            </div>
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="<?= e($input['email']) ?>" required maxlength="254" class="input">
                <?= $error('email') ?>
            </div>
            <div>
                <label for="password" class="label"><?= $isNew ? 'Password' : 'New password' ?></label>
                <input id="password" type="password" name="password" <?= $isNew ? 'required' : '' ?> minlength="<?= Passwords::MIN_LENGTH ?>" maxlength="<?= Passwords::MAX_BYTES ?>" autocomplete="new-password" class="input" placeholder="<?= $isNew ? 'At least ' . Passwords::MIN_LENGTH . ' characters' : 'Leave blank to keep the current password' ?>">
                <?= $error('password') ?>
            </div>
            <div>
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" <?= $isNew ? 'required' : '' ?> maxlength="<?= Passwords::MAX_BYTES ?>" autocomplete="new-password" class="input">
            </div>

            <fieldset class="rounded-lg border border-gray-700 p-4">
                <legend class="px-1 text-sm font-medium text-white">Roles</legend>
                <div class="space-y-2">
                    <?php foreach ($roles as $role): ?>
                        <?php
                        $checked = in_array($role['ID'], $input['roles'], true);
                        $locked = $role['ID'] === Roles::SITE_ADMIN && !$canGrantSiteAdmin;
                        ?>
                        <label class="flex items-start gap-2 text-sm text-gray-300 <?= $locked ? 'opacity-60' : '' ?>">
                            <input type="checkbox" name="roles[]" value="<?= e($role['ID']) ?>" class="checkbox mt-0.5" <?= $checked ? 'checked' : '' ?> <?= $locked ? 'disabled' : '' ?>>
                            <span><span class="font-medium text-white"><?= e($role['ID']) ?></span> — <?= e($role['Description']) ?></span>
                        </label>
                    <?php endforeach; ?>
                </div>
                <?= $error('roles') ?>
            </fieldset>

            <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <a href="/admin/authors" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $isNew ? 'Add author' : 'Save changes' ?></button>
            </div>
        </form>
    </div>
</section>
