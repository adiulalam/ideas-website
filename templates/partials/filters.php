<?php
/**
 * @var App\Ideas\IdeaFilters $filters
 * @var list<array{ID: int, Name: string}> $authors
 * @var list<array{ID: int, Name: string, Image: string}> $categories
 */
use App\Ideas\IdeaFilters;
?>
<form method="get" action="/" class="grid gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_2fr_auto]" role="search">
    <div>
        <label for="filter-author" class="sr-only">Author</label>
        <select id="filter-author" name="author" class="input">
            <option value="">All authors</option>
            <?php foreach ($authors as $author): ?>
                <option value="<?= $author['ID'] ?>" <?= $filters->authorId === $author['ID'] ? 'selected' : '' ?>><?= e($author['Name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="filter-category" class="sr-only">Category</label>
        <select id="filter-category" name="category" class="input">
            <option value="">All categories</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= $category['ID'] ?>" <?= $filters->categoryId === $category['ID'] ? 'selected' : '' ?>><?= e($category['Name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label for="filter-q" class="sr-only">Search ideas</label>
        <input id="filter-q" type="search" name="q" value="<?= e($filters->search) ?>" maxlength="<?= IdeaFilters::MAX_SEARCH_LENGTH ?>" placeholder="Search ideas" class="input">
    </div>
    <div class="flex gap-2">
        <?php if ($filters->sort !== IdeaFilters::DEFAULT_SORT): ?>
            <input type="hidden" name="sort" value="<?= e($filters->sort) ?>">
        <?php endif; ?>
        <?php if ($filters->perPage !== IdeaFilters::DEFAULT_PER_PAGE): ?>
            <input type="hidden" name="per_page" value="<?= e($filters->perPage) ?>">
        <?php endif; ?>
        <button type="submit" class="btn btn-primary flex-1">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Search
        </button>
        <?php if ($filters->isFiltered()): ?>
            <a href="/" class="btn btn-secondary">Clear</a>
        <?php endif; ?>
    </div>
</form>
