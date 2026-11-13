<?php
/**
 * @var App\View $view
 * @var App\Ideas\IdeaFilters $filters
 * @var list<array<string, mixed>> $ideas
 * @var array<int, int> $myVotes
 * @var array<string, mixed>|null $currentUser
 * @var int $total
 * @var int $pages
 */
use App\Support\Url;

$returnTo = Url::to('/', $filters->toQuery());
?>
<section class="mx-auto max-w-6xl px-4 py-6">
    <h1 class="sr-only">Ideas</h1>

    <div class="card mb-6 space-y-4 p-4">
        <?= $view->partial('partials/filters', compact('filters', 'authors', 'categories')) ?>
        <?= $view->partial('partials/list_controls', compact('filters', 'total')) ?>
    </div>

    <?php if ($ideas === []): ?>
        <div class="card p-10 text-center">
            <p class="text-lg text-gray-300"><?= $filters->isFiltered() ? 'No ideas match your search.' : 'No ideas have been posted yet.' ?></p>
            <a href="/ideas/new" class="btn btn-primary mt-4">Post the first idea</a>
        </div>
    <?php else: ?>
        <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
            <?php foreach ($ideas as $idea): ?>
                <?= $view->partial('partials/idea_card', [
                    'idea' => $idea,
                    'currentUser' => $currentUser,
                    'myVote' => $myVotes[$idea['ID']] ?? null,
                    'standalone' => false,
                    'returnTo' => $returnTo . '#idea-' . $idea['ID'],
                ]) ?>
            <?php endforeach; ?>
        </div>

        <?= $view->partial('partials/pagination', compact('filters', 'pages')) ?>
    <?php endif; ?>
</section>
