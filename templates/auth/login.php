<?php
/**
 * @var App\View $view
 * @var App\Security\Csrf $csrf
 * @var string $email
 * @var array<string, string> $errors
 */
?>
<section class="flex justify-center px-4 py-12">
    <div class="card w-full max-w-md p-6 sm:p-8">
        <h1 class="mb-6 text-2xl font-bold text-white">Log in</h1>

        <?php if (isset($errors['form'])): ?>
            <p class="mb-4 rounded-lg border border-red-800 bg-red-950/60 px-4 py-3 text-sm text-red-200" role="alert"><?= e($errors['form']) ?></p>
        <?php endif; ?>

        <form method="post" action="/login" class="space-y-5">
            <?= $csrf->field() ?>
            <div>
                <label for="email" class="label">Email</label>
                <input id="email" type="email" name="email" value="<?= e($email) ?>" required autocomplete="email" autofocus class="input" placeholder="you@example.com">
            </div>
            <div>
                <label for="password" class="label">Password</label>
                <input id="password" type="password" name="password" required autocomplete="current-password" class="input" placeholder="••••••••">
            </div>
            <?= $view->partial('partials/captcha') ?>
            <button type="submit" class="btn btn-primary w-full">Log in</button>
            <p class="text-sm text-gray-400">Don’t have an account? <a href="/register" class="font-medium text-blue-400 hover:underline">Register here</a></p>
        </form>
    </div>
</section>
