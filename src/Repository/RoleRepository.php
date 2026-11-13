<?php

declare(strict_types=1);

namespace App\Repository;

final class RoleRepository extends Repository
{
    /** @return list<array{ID: string, Description: string}> */
    public function all(): array
    {
        return array_map(
            static fn (array $row): array => ['ID' => (string) $row['ID'], 'Description' => (string) $row['Description']],
            $this->select('SELECT ID, Description FROM Role ORDER BY ID'),
        );
    }
}
