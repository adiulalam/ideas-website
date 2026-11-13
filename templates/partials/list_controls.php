<?php
/**
 * Sort and page-size selectors (auto-submit with JS, "Apply" button without).
 *
 * @var App\Ideas\IdeaFilters $filters
 * @var int $total
 */
use App\Ideas\IdeaFilters;
?>
<form method="get" action="/" class="flex flex-wrap items-center justify-between gap-3">
    <?php foreach (['author' => $filters->authorId, 'category' => $filters->categoryId, 'q' => $filters->search] as $name => $value): ?>
        <?php if ($value !== null && $value !== ''): ?>
            <input type="hidden" name="<?= e($name) ?>" value="<?= e($value) ?>">
        <?php endif; ?>
    <?php endforeach; ?>

    <p class="text-sm text-gray-400"><?= e(number_format($total)) ?> <?= $total === 1 ? 'idea' : 'ideas' ?></p>

    <div class="flex items-center gap-2">
        <label for="sort" class="sr-only">Sort by</label>
        <select id="sort" name="sort" class="input w-auto" data-autosubmit>
            <?php foreach (IdeaFilters::SORT_LABELS as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= $filters->sort === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <label for="per_page" class="sr-only">Ideas per page</label>
        <select id="per_page" name="per_page" class="input w-auto" data-autosubmit>
            <?php foreach (IdeaFilters::PER_PAGE_OPTIONS as $option): ?>
                <option value="<?= $option ?>" <?= $filters->perPage === $option ? 'selected' : '' ?>><?= $option ?> per page</option>
            <?php endforeach; ?>
        </select>
        <noscript><button type="submit" class="btn btn-secondary">Apply</button></noscript>
    </div>
</form>
