<?php

declare(strict_types=1);

namespace App\Ideas;

/**
 * Validated search/sort/paging options for the ideas list.
 *
 * The old code concatenated `$_GET['orderBy']` and `$_GET['limitRecords']`
 * straight into SQL (unauthenticated SQL injection). Sorting now goes through
 * a fixed whitelist and paging values are integers from a fixed set.
 */
final class IdeaFilters
{
    /** @var array<string, string> sort key => ORDER BY clause (trusted, never user input) */
    public const SORTS = [
        'newest' => 'i.IdeaDate DESC, i.ID DESC',
        'oldest' => 'i.IdeaDate ASC, i.ID ASC',
        'votes' => 'Votes DESC, i.IdeaDate DESC, i.ID DESC',
        'votes_asc' => 'Votes ASC, i.IdeaDate DESC, i.ID DESC',
        'az' => 'i.IdeaText ASC, i.ID ASC',
        'za' => 'i.IdeaText DESC, i.ID DESC',
    ];

    /** @var array<string, string> */
    public const SORT_LABELS = [
        'newest' => 'Newest first',
        'oldest' => 'Oldest first',
        'votes' => 'Most votes',
        'votes_asc' => 'Fewest votes',
        'az' => 'Text A–Z',
        'za' => 'Text Z–A',
    ];

    /** Values the old UI put in ?orderBy=, so old links keep working. */
    private const LEGACY_SORTS = [
        'IdeaDate DESC' => 'newest',
        'IdeaDate ASC' => 'oldest',
        'Vote DESC' => 'votes',
        'Vote ASC' => 'votes_asc',
        'IdeaText ASC' => 'az',
        'IdeaText DESC' => 'za',
    ];

    public const DEFAULT_SORT = 'newest';
    public const PER_PAGE_OPTIONS = [10, 20, 50, 100];
    public const DEFAULT_PER_PAGE = 10;
    public const MAX_SEARCH_LENGTH = 100;

    public function __construct(
        public readonly ?int $authorId = null,
        public readonly ?int $categoryId = null,
        public readonly string $search = '',
        public readonly string $sort = self::DEFAULT_SORT,
        public readonly int $perPage = self::DEFAULT_PER_PAGE,
        public readonly int $page = 1,
    ) {
    }

    /**
     * Build from the query string. Accepts the current parameter names and the
     * legacy ones (Author, Category, text, orderBy, limitRecords).
     *
     * @param array<array-key, mixed> $query
     */
    public static function fromQuery(array $query): self
    {
        $pick = static function (string ...$keys) use ($query): ?string {
            foreach ($keys as $key) {
                $value = $query[$key] ?? null;
                if (is_string($value) && trim($value) !== '') {
                    return trim($value);
                }
            }

            return null;
        };
        $positiveInt = static function (?string $value): ?int {
            if ($value === null || preg_match('/^\d{1,9}$/', $value) !== 1 || (int) $value < 1) {
                return null;
            }

            return (int) $value;
        };

        $sort = $pick('sort');
        if ($sort === null || !isset(self::SORTS[$sort])) {
            $legacy = $pick('orderBy');
            $sort = $legacy !== null ? (self::LEGACY_SORTS[$legacy] ?? self::DEFAULT_SORT) : self::DEFAULT_SORT;
        }

        $perPage = $positiveInt($pick('per_page', 'limitRecords')) ?? self::DEFAULT_PER_PAGE;
        if (!in_array($perPage, self::PER_PAGE_OPTIONS, true)) {
            $perPage = self::DEFAULT_PER_PAGE;
        }

        return new self(
            authorId: $positiveInt($pick('author', 'Author')),
            categoryId: $positiveInt($pick('category', 'Category')),
            search: mb_substr($pick('q', 'text') ?? '', 0, self::MAX_SEARCH_LENGTH, 'UTF-8'),
            sort: $sort,
            perPage: $perPage,
            page: $positiveInt($pick('page')) ?? 1,
        );
    }

    public function orderBy(): string
    {
        return self::SORTS[$this->sort] ?? self::SORTS[self::DEFAULT_SORT];
    }

    public function withPage(int $page): self
    {
        return new self($this->authorId, $this->categoryId, $this->search, $this->sort, $this->perPage, max(1, $page));
    }

    /**
     * Query parameters for links, omitting defaults.
     *
     * @param array<string, scalar|null> $overrides
     * @return array<string, scalar|null>
     */
    public function toQuery(array $overrides = []): array
    {
        $query = [
            'author' => $this->authorId,
            'category' => $this->categoryId,
            'q' => $this->search,
            'sort' => $this->sort === self::DEFAULT_SORT ? null : $this->sort,
            'per_page' => $this->perPage === self::DEFAULT_PER_PAGE ? null : $this->perPage,
            'page' => $this->page > 1 ? $this->page : null,
        ];

        return array_merge($query, $overrides);
    }

    public function isFiltered(): bool
    {
        return $this->authorId !== null || $this->categoryId !== null || $this->search !== '';
    }
}
