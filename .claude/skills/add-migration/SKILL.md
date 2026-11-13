---
name: add-migration
description: Add a safe database schema change to Ideal Ideas (new column, index, table or widened column) as an idempotent PHP migration that runs automatically on container start against the hand-made production MySQL schema. Use whenever a change needs the database schema to change.
---

# Adding a migration

Production's schema was created by hand and is only approximated by
`database/schema.sql`. Migrations must therefore **inspect before they alter**
and never assume a column's current type, length or nullability.

## Steps

1. Create `database/migrations/YYYY_MM_DD_NNNNNN_short_description.php` (today's
   date, sortable). Files run in filename order and are recorded in the
   `SchemaMigration` table.
2. Return a closure taking `PDO`:

   ```php
   <?php

   declare(strict_types=1);

   return static function (PDO $pdo): void {
       $exists = $pdo->query(
           "SELECT COUNT(*) FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'Idea' AND COLUMN_NAME = 'UpdatedAt'"
       )->fetchColumn();
       if ((int) $exists > 0) {
           return;            // already applied by hand or by an earlier run
       }
       $pdo->exec('ALTER TABLE Idea ADD COLUMN UpdatedAt DATETIME NULL');
   };
   ```

3. Rules:
   - Idempotent: running it twice, or on a database already in the target
     state, must be a no-op.
   - Additive and non-destructive: never `DROP` columns or tables or delete
     data. New columns must be `NULL` or have a `DEFAULT`, so old rows and old
     code keep working.
   - Preserve what you don't control: when modifying a column, re-state its
     current nullability (see `2026_11_12_000001_widen_author_password.php`).
   - MySQL DDL auto-commits, so don't wrap it in a transaction.
   - Keep the legacy naming style (`PascalCase` tables and columns).
4. Do **not** edit `database/schema.sql`. It is the legacy baseline.
5. Test it locally:

   ```bash
   docker compose -f docker-compose.dev.yml up --build -d
   docker compose -f docker-compose.dev.yml logs app | grep '\[migrate\]'   # "applied ..."
   docker compose -f docker-compose.dev.yml restart app
   docker compose -f docker-compose.dev.yml logs app | grep '\[migrate\]'   # "nothing to migrate"
   ```

   Then test once more from a fresh database (`down -v`, `up --build -d`).
6. Code that depends on the new schema must cope with the migration not having
   run yet: a failed migration only logs a warning, and the site keeps
   serving. See `AuthorRepository::passwordColumnFits()` for the pattern.
7. In the PR description, say exactly what the migration changes on
   production. It runs automatically on the next deploy.
