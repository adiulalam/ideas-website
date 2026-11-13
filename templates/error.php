<?php
/**
 * @var int $status
 * @var string $title
 * @var string $message
 */
?>
<section class="flex justify-center px-4 py-16">
    <div class="card w-full max-w-lg p-8 text-center">
        <p class="text-5xl font-bold text-gray-500"><?= e($status) ?></p>
        <h1 class="mt-2 text-2xl font-bold text-white"><?= e($title) ?></h1>
        <p class="mt-4 whitespace-pre-line text-gray-300"><?= e($message) ?></p>
        <a href="/" class="btn btn-primary mt-8">Back to the ideas</a>
    </div>
</section>
