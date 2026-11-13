<?php
/** @var list<array{type: string, message: string}> $messages */
?>
<?php if ($messages !== []): ?>
    <div class="mx-auto mt-4 w-full max-w-6xl space-y-2 px-4" role="status" aria-live="polite">
        <?php foreach ($messages as $message): ?>
            <?php $isError = $message['type'] === 'error'; ?>
            <div data-flash class="flex items-start justify-between gap-4 rounded-lg border px-4 py-3 text-sm <?= $isError ? 'border-red-800 bg-red-950/60 text-red-200' : 'border-green-800 bg-green-950/60 text-green-200' ?>">
                <p><?= e($message['message']) ?></p>
                <button type="button" data-dismiss class="-my-1 rounded px-1 text-lg leading-none opacity-70 hover:opacity-100" aria-label="Dismiss">&times;</button>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
