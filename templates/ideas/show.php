<?php
/**
 * @var App\View $view
 * @var App\Security\Csrf $csrf
 * @var array<string, mixed> $idea
 * @var list<array<string, mixed>> $comments
 * @var array<int, int> $myVotes
 * @var array<string, mixed>|null $currentUser
 */
use App\Controllers\CommentController;
use App\Support\Time;

$ideaId = (int) $idea['ID'];
$userId = $currentUser !== null ? (int) $currentUser['Author_ID'] : null;
?>
<section class="mx-auto max-w-3xl px-4 py-6">
    <a href="/" class="mb-4 inline-block text-sm text-blue-400 hover:underline">&larr; All ideas</a>

    <?= $view->partial('partials/idea_card', [
        'idea' => $idea,
        'currentUser' => $currentUser,
        'myVote' => $myVotes[$ideaId] ?? null,
        'standalone' => true,
        'returnTo' => '/idea?id=' . $ideaId,
    ]) ?>

    <section id="comments" class="mt-8" aria-labelledby="comments-heading">
        <h2 id="comments-heading" class="mb-4 text-xl font-semibold text-white">Comments (<?= count($comments) ?>)</h2>

        <?php if ($currentUser !== null): ?>
            <form method="post" action="/comments" class="card mb-6 overflow-hidden">
                <?= $csrf->field() ?>
                <input type="hidden" name="idea_id" value="<?= $ideaId ?>">
                <label for="comment" class="sr-only">Your comment</label>
                <textarea id="comment" name="comment" rows="4" required maxlength="<?= CommentController::MAX_LENGTH ?>"
                          class="block w-full border-0 bg-gray-800 p-4 text-sm text-white placeholder:text-gray-400 focus:ring-0 focus:outline-none"
                          placeholder="Write a comment…"></textarea>
                <div class="flex justify-end border-t border-gray-700 bg-gray-800 px-3 py-2">
                    <button type="submit" class="btn btn-primary btn-sm">Post comment</button>
                </div>
            </form>
        <?php else: ?>
            <p class="card mb-6 p-4 text-sm text-gray-300">
                <a href="/login?next=<?= e(rawurlencode('/idea?id=' . $ideaId . '#comments')) ?>" class="font-medium text-blue-400 hover:underline">Log in</a>
                or <a href="/register" class="font-medium text-blue-400 hover:underline">create an account</a> to join the discussion.
            </p>
        <?php endif; ?>

        <?php if ($comments === []): ?>
            <p class="text-sm text-gray-400">No comments yet.</p>
        <?php else: ?>
            <ul class="space-y-3">
                <?php foreach ($comments as $comment): ?>
                    <li class="card p-4">
                        <p class="text-sm break-words whitespace-pre-line text-gray-200"><?= e($comment['Comment']) ?></p>
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-2 text-xs text-gray-400">
                            <span>By <?= e($comment['AuthorName']) ?> · <time datetime="<?= e($comment['Time']) ?>"><?= e(Time::ago((string) $comment['Time'])) ?></time></span>
                            <?php if ($userId !== null && $comment['AuthorID'] === $userId): ?>
                                <form method="post" action="/comments/delete" data-confirm="Are you sure you want to delete this comment?">
                                    <?= $csrf->field() ?>
                                    <input type="hidden" name="id" value="<?= (int) $comment['CommentID'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </section>
</section>
