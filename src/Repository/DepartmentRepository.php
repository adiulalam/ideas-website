<?php

declare(strict_types=1);

namespace App\Repository;

use PDO;

final class DepartmentRepository extends Repository
{
    /** @return list<array{ID: int, Name: string}> */
    public function all(): array
    {
        return array_map(
            static fn (array $row): array => ['ID' => (int) $row['ID'], 'Name' => (string) $row['Name']],
            $this->select('SELECT ID, Name FROM Department ORDER BY Name'),
        );
    }

    /**
     * @param list<int> $ids
     * @return list<int>
     */
    public function existingIds(array $ids): array
    {
        if ($ids === []) {
            return [];
        }

        return array_map('intval', $this->run('SELECT ID FROM Department WHERE ID IN (' . self::placeholders(count($ids)) . ')', $ids)->fetchAll(PDO::FETCH_COLUMN));
    }
}
