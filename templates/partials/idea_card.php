<?php
/**
 * One idea, used on the list and on the idea page.
 *
 * @var App\Security\Csrf $csrf
 * @var App\Services\HtmlSanitizer $sanitizer
 * @var array<string, mixed> $idea
 * @var array<string, mixed>|null $currentUser
 * @var int|null $myVote
 * @var bool $standalone True on the idea's own page.
 * @var string $returnTo Where to come back to after voting.
 */
use App\Security\Roles;
use App\Support\Images;
use App\Support\Time;

$ideaId = (int) $idea['ID'];
$ideaUrl = '/idea?id=' . $ideaId;
$imageSrc = Images::src($idea['Image']);
$roles = is_array($currentUser['roles'] ?? null) ? $currentUser['roles'] : [];
$canEdit = $currentUser !== null && (
    (int) $idea['AuthorID'] === (int) $currentUser['Author_ID']
    || array_intersect([Roles::SITE_ADMIN, Roles::CONTENT_EDITOR], $roles) !== []
);
$comments = (int) $idea['CommentCount'];
?>
<article id="idea-<?= $ideaId ?>" class="card flex flex-col overflow-hidden">
    <?php if ($imageSrc !== null): ?>
        <?php if ($standalone): ?>
            <img src="<?= e($imageSrc) ?>" alt="" class="max-h-[32rem] w-full bg-gray-900 object-contain" referrerpolicy="no-referrer">
        <?php else: ?>
            <a href="<?= e($ideaUrl) ?>" tabindex="-1" aria-hidden="true">
                <img src="<?= e($imageSrc) ?>" alt="" loading="lazy" class="aspect-video w-full bg-gray-900 object-cover" referrerpolicy="no-referrer">
            </a>
        <?php endif; ?>
    <?php endif; ?>

    <div class="flex flex-1 flex-col gap-3 p-4">
        <div class="flex items-center justify-between gap-2 text-xs text-gray-400">
            <a href="/?author=<?= (int) $idea['AuthorID'] ?>" class="hover:text-gray-200 hover:underline">By <?= e($idea['AuthorName']) ?></a>
            <time datetime="<?= e($idea['IdeaDate']) ?>" class="italic"><?= e(Time::date((string) $idea['IdeaDate'])) ?></time>
        </div>

        <?php if ($standalone): ?>
            <h1 class="idea-text text-2xl font-bold break-words text-white"><?= $sanitizer->render((string) $idea['IdeaText']) ?></h1>
        <?php else: ?>
            <h2 class="idea-text text-xl font-bold break-words text-white"><?= $sanitizer->render((string) $idea['IdeaText']) ?></h2>
        <?php endif; ?>

        <div class="mt-auto flex flex-wrap items-center justify-between gap-3 pt-2">
            <div class="flex flex-wrap items-center gap-2">
                <?php if (!$standalone): ?>
                    <a href="<?= e($ideaUrl) ?>#comments" class="btn btn-primary btn-sm">
                        <?= $comments === 1 ? '1 comment' : e($comments) . ' comments' ?>
                    </a>
                <?php endif; ?>
                <?php if ($canEdit): ?>
                    <a href="/ideas/edit?id=<?= $ideaId ?>" class="btn btn-secondary btn-sm">Edit</a>
                    <form method="post" action="/ideas/delete" data-confirm="Are you sure you want to delete this idea? Its comments and votes will be deleted too.">
                        <?= $csrf->field() ?>
                        <input type="hidden" name="id" value="<?= $ideaId ?>">
                        <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                    </form>
                <?php endif; ?>
            </div>

            <form method="post" action="/ideas/vote" class="flex items-center gap-1" aria-label="Vote on this idea">
                <?= $csrf->field() ?>
                <input type="hidden" name="id" value="<?= $ideaId ?>">
                <input type="hidden" name="return_to" value="<?= e($returnTo) ?>">
                <button type="submit" name="direction" value="up" aria-label="Upvote" aria-pressed="<?= $myVote === 1 ? 'true' : 'false' ?>" <?= $myVote === 1 ? 'disabled' : '' ?>
                        class="rounded-lg p-1.5 text-white focus:ring-4 focus:ring-blue-800 focus:outline-none <?= $myVote === 1 ? 'bg-blue-600' : 'bg-gray-600 hover:bg-blue-700' ?>">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12.781 2.375c-.381-.475-1.181-.475-1.562 0l-8 10A1.001 1.001 0 0 0 4 14h4v7a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1v-7h4a1.001 1.001 0 0 0 .781-1.625l-8-10zM15 12h-1v8h-4v-8H6.081L12 4.601L17.919 12H15z"/></svg>
                </button>
                <span class="min-w-8 text-center text-sm font-semibold text-white" title="Score"><?= e((int) $idea['Votes']) ?></span>
                <button type="submit" name="direction" value="down" aria-label="Downvote" aria-pressed="<?= $myVote === -1 ? 'true' : 'false' ?>" <?= $myVote === -1 ? 'disabled' : '' ?>
                        class="rounded-lg p-1.5 text-white focus:ring-4 focus:ring-red-800 focus:outline-none <?= $myVote === -1 ? 'bg-red-600' : 'bg-gray-600 hover:bg-red-700' ?>">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M20.901 10.566A1.001 1.001 0 0 0 20 10h-4V3a1 1 0 0 0-1-1H9a1 1 0 0 0-1 1v7H4a1.001 1.001 0 0 0-.781 1.625l8 10a1 1 0 0 0 1.562 0l8-10c.24-.301.286-.712.12-1.059zM12 19.399L6.081 12H10V4h4v8h3.919L12 19.399z"/></svg>
                </button>
            </form>
        </div>
    </div>
</article>
