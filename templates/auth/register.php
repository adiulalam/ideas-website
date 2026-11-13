<?php
/**
 * @var App\View $view
 * @var App\Security\Csrf $csrf
 * @var array{name: string, email: string} $input
 * @var array<string, string> $errors
 */
use App\Security\Passwords;

$error = static fn (string $field): string => $view->partial('partials/form_error', ['errors' => $errors, 'field' => $field]);
?>
<section class="flex justify-center px-4 py-12">
    <div class="card w-full max-w-md p-6 sm:p-8">
        <h1 class="mb-6 text-2xl font-bold text-white">Create an account</h1>

        <?php if (isset($errors['form'])): ?>
            <p class="mb-4 rounded-lg border border-red-800 bg-red-950/60 px-4 py-3 text-sm text-red-200" role="alert"><?= e($errors['form']) ?></p>
        <?php endif; ?>

        <form method="post" action="/register" class="space-y-5">
            <?= $csrf->field() ?>
            <div>
                <label for="name" class="label">Your name</label>
                <input id="name" type="text" name="name" value="<?= e($input['name']) ?>" required maxlength="100" autocomplete="name" class="input" placeholder="Joe Davis">
                <?= $error('name') ?>
            </div>
            <div>
                <label for="email" class="label">Your email</label>
                <input id="email" type="email" name="email" value="<?= e($input['email']) ?>" required maxlength="254" autocomplete="email" class="input" placeholder="joe@example.com">
                <?= $error('email') ?>
            </div>
            <div>
                <label for="password" class="label">Password</label>
                <input id="password" type="password" name="password" required minlength="<?= Passwords::MIN_LENGTH ?>" maxlength="<?= Passwords::MAX_BYTES ?>" autocomplete="new-password" class="input" placeholder="At least <?= Passwords::MIN_LENGTH ?> characters">
                <?= $error('password') ?>
            </div>
            <div>
                <label for="password_confirmation" class="label">Confirm password</label>
                <input id="password_confirmation" type="password" name="password_confirmation" required minlength="<?= Passwords::MIN_LENGTH ?>" maxlength="<?= Passwords::MAX_BYTES ?>" autocomplete="new-password" class="input" placeholder="••••••••">
            </div>
            <?= $view->partial('partials/captcha') ?>
            <button type="submit" class="btn btn-primary w-full">Create an account</button>
            <p class="text-sm text-gray-400">Already have an account? <a href="/login" class="font-medium text-blue-400 hover:underline">Log in here</a></p>
        </form>
    </div>
</section>
