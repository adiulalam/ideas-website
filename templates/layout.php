<?php
/**
 * Page shell.
 *
 * @var App\View $view
 * @var App\Services\Captcha $captcha
 * @var App\Support\Flash $flash
 * @var string $content
 */
$title = $title ?? 'Ideal Ideas';
$currentUser = $currentUser ?? null;
$useCaptcha = ($useCaptcha ?? false) && $captcha->enabled();
$asset = static function (string $path): string {
    $file = dirname(__DIR__) . '/public' . $path;

    return $path . (is_file($file) ? '?v=' . filemtime($file) : '');
};
?>
<!doctype html>
<html lang="en" class="h-full bg-gray-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ideal Ideas: post ideas, vote on them and discuss them.">
    <title><?= e($title) ?> · Ideal Ideas</title>
    <link rel="stylesheet" href="<?= e($asset('/assets/app.css')) ?>">
    <script src="<?= e($asset('/assets/app.js')) ?>" defer></script>
    <?php if ($useCaptcha): ?>
        <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <?php endif; ?>
</head>
<body class="flex min-h-full flex-col bg-gray-900 text-gray-100 antialiased">
    <a href="#main" class="sr-only focus:not-sr-only focus:absolute focus:top-2 focus:left-2 focus:z-50 focus:rounded focus:bg-blue-600 focus:px-3 focus:py-2 focus:text-white">Skip to content</a>

    <?= $view->partial('partials/nav', ['currentUser' => $currentUser]) ?>
    <?= $view->partial('partials/flash', ['messages' => $flash->pull()]) ?>

    <main id="main" class="flex-1<?= isset($fab) ? ' pb-20' : '' ?>">
        <?= $content ?>
    </main>

    <?= $view->partial('partials/footer') ?>

    <?php if (isset($fab)): ?>
        <?php $fabHref = $currentUser !== null ? $fab['href'] : '/login?next=' . rawurlencode($fab['href']); ?>
        <a href="<?= e($fabHref) ?>" title="<?= e($fab['label']) ?>" aria-label="<?= e($fab['label']) ?>"
           class="fixed right-5 bottom-5 z-40 inline-flex h-14 w-14 items-center justify-center rounded-full bg-blue-600 text-white shadow-lg shadow-black/40 transition hover:bg-blue-700 focus:ring-4 focus:ring-blue-800 focus:outline-none">
            <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
        </a>
    <?php endif; ?>

    <dialog id="confirm-dialog" class="m-auto w-[calc(100%-2rem)] max-w-md rounded-lg border border-gray-600 bg-gray-700 p-6 text-center text-gray-200 shadow-xl backdrop:bg-black/60">
        <form method="dialog">
            <svg class="mx-auto mb-4 h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <p data-confirm-message class="mb-6 text-lg text-gray-200">Are you sure?</p>
            <div class="flex justify-center gap-3">
                <button type="submit" value="confirm" class="btn btn-danger">Yes, I’m sure</button>
                <button type="submit" value="cancel" class="btn btn-secondary" autofocus>No, cancel</button>
            </div>
        </form>
    </dialog>
</body>
</html>
