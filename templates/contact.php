<?php
/**
 * @var App\View $view
 * @var App\Security\Csrf $csrf
 * @var array{name: string, email: string, subject: string, message: string} $input
 * @var array<string, string> $errors
 */
$error = static fn (string $field): string => $view->partial('partials/form_error', ['errors' => $errors, 'field' => $field]);
?>
<section class="flex justify-center px-4 py-12">
    <div class="card w-full max-w-lg p-6 sm:p-8">
        <h1 class="mb-2 text-2xl font-bold text-white">Contact me</h1>
        <p class="mb-6 text-sm text-gray-400">Your message goes straight to my inbox and I’ll reply to the email address you give.</p>

        <?php if (isset($errors['form'])): ?>
            <p class="mb-4 rounded-lg border border-red-800 bg-red-950/60 px-4 py-3 text-sm text-red-200" role="alert"><?= e($errors['form']) ?></p>
        <?php endif; ?>

        <form method="post" action="/contact" class="space-y-5">
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
                <label for="subject" class="label">Subject</label>
                <input id="subject" type="text" name="subject" value="<?= e($input['subject']) ?>" maxlength="150" class="input" placeholder="What is it about?">
                <?= $error('subject') ?>
            </div>
            <div>
                <label for="message" class="label">Message</label>
                <textarea id="message" name="message" rows="5" required maxlength="5000" class="input" placeholder="Write your message"><?= e($input['message']) ?></textarea>
                <?= $error('message') ?>
            </div>
            <?= $view->partial('partials/captcha') ?>
            <button type="submit" class="btn btn-primary w-full">Send</button>
        </form>
    </div>
</section>
