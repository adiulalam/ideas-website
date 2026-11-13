<?php
/**
 * @var App\Ideas\IdeaFilters $filters
 * @var int $pages
 */
use App\Support\Url;

$page = $filters->page;
$href = static fn (int $p): string => Url::to('/', $filters->toQuery(['page' => $p > 1 ? $p : null]));
$window = array_unique(array_filter(
    [1, $page - 2, $page - 1, $page, $page + 1, $page + 2, $pages],
    static fn (int $p): bool => $p >= 1 && $p <= $pages,
));
sort($window);
$base = 'inline-flex min-w-10 items-center justify-center rounded-lg px-3 py-2 text-sm font-medium';
?>
<?php if ($pages > 1): ?>
    <nav class="mt-8 flex flex-wrap items-center justify-center gap-1" aria-label="Pagination">
        <?php if ($page > 1): ?>
            <a href="<?= e($href($page - 1)) ?>" class="<?= $base ?> bg-gray-700 text-gray-200 hover:bg-gray-600" rel="prev">&laquo; Prev</a>
        <?php endif; ?>

        <?php $previous = 0; ?>
        <?php foreach ($window as $p): ?>
            <?php if ($p - $previous > 1): ?>
                <span class="px-2 text-gray-500">…</span>
            <?php endif; ?>
            <?php if ($p === $page): ?>
                <span class="<?= $base ?> bg-blue-600 text-white" aria-current="page"><?= $p ?></span>
            <?php else: ?>
                <a href="<?= e($href($p)) ?>" class="<?= $base ?> bg-gray-700 text-gray-200 hover:bg-gray-600"><?= $p ?></a>
            <?php endif; ?>
            <?php $previous = $p; ?>
        <?php endforeach; ?>

        <?php if ($page < $pages): ?>
            <a href="<?= e($href($page + 1)) ?>" class="<?= $base ?> bg-gray-700 text-gray-200 hover:bg-gray-600" rel="next">Next &raquo;</a>
        <?php endif; ?>
    </nav>
<?php endif; ?>
