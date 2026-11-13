<?php

declare(strict_types=1);

namespace Tests;

use App\Ideas\IdeaFilters;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class IdeaFiltersTest extends TestCase
{
    public function testDefaults(): void
    {
        $filters = IdeaFilters::fromQuery([]);

        self::assertNull($filters->authorId);
        self::assertNull($filters->categoryId);
        self::assertSame('', $filters->search);
        self::assertSame('newest', $filters->sort);
        self::assertSame(10, $filters->perPage);
        self::assertSame(1, $filters->page);
        self::assertSame([], array_filter($filters->toQuery(), static fn ($v) => $v !== null && $v !== ''));
    }

    /** These payloads used to be concatenated straight into ORDER BY / LIMIT. */
    #[DataProvider('injectionPayloads')]
    public function testSqlInjectionPayloadsFallBackToSafeValues(string $orderBy, string $limit, string $page): void
    {
        $filters = IdeaFilters::fromQuery(['orderBy' => $orderBy, 'limitRecords' => $limit, 'page' => $page]);

        self::assertContains($filters->orderBy(), IdeaFilters::SORTS);
        self::assertContains($filters->perPage, IdeaFilters::PER_PAGE_OPTIONS);
        self::assertGreaterThanOrEqual(1, $filters->page);
    }

    /** @return iterable<string, array{string, string, string}> */
    public static function injectionPayloads(): iterable
    {
        yield 'subquery in order by' => ["(SELECT IF(SUBSTR(Password,1,1)='a',IdeaDate,IdeaText) FROM Author LIMIT 1)", '10', '1'];
        yield 'into outfile via limit' => ['IdeaDate DESC', "10 INTO OUTFILE '/var/www/html/x.php'", '1'];
        yield 'zero limit (division by zero)' => ['IdeaDate DESC', '0', '1'];
        yield 'negative page' => ['IdeaDate DESC', '10', '-5'];
        yield 'page expression' => ['IdeaDate DESC', '10', '1 OR 1=1'];
    }

    public function testLegacyParameterNamesAreUnderstood(): void
    {
        $filters = IdeaFilters::fromQuery([
            'Author' => '3',
            'Category' => '2',
            'text' => 'curry',
            'orderBy' => 'Vote DESC',
            'limitRecords' => '50',
            'page' => '2',
        ]);

        self::assertSame(3, $filters->authorId);
        self::assertSame(2, $filters->categoryId);
        self::assertSame('curry', $filters->search);
        self::assertSame('votes', $filters->sort);
        self::assertSame(50, $filters->perPage);
        self::assertSame(2, $filters->page);
    }

    public function testArrayParametersAreIgnored(): void
    {
        $filters = IdeaFilters::fromQuery(['q' => ['x'], 'author' => ['1'], 'sort' => ['votes']]);

        self::assertSame('', $filters->search);
        self::assertNull($filters->authorId);
        self::assertSame('newest', $filters->sort);
    }

    public function testSearchIsTruncated(): void
    {
        $filters = IdeaFilters::fromQuery(['q' => str_repeat('é', 500)]);

        self::assertSame(IdeaFilters::MAX_SEARCH_LENGTH, mb_strlen($filters->search));
    }

    public function testToQueryOmitsDefaultsAndAppliesOverrides(): void
    {
        $filters = IdeaFilters::fromQuery(['author' => '4', 'sort' => 'votes', 'page' => '3']);

        self::assertSame(
            ['author' => 4, 'category' => null, 'q' => '', 'sort' => 'votes', 'per_page' => null, 'page' => null],
            $filters->toQuery(['page' => null]),
        );
    }
}
