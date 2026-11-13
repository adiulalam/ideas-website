---
paths:
  - "src/Repository/**"
  - "database/**"
  - "bin/migrate.php"
  - "src/Migrations/**"
---

# Database rules

- All SQL lives in `src/Repository/`. Use `$this->run()`, `select()` or
  `selectOne()` with bound parameters. Ints are bound as `PARAM_INT`, so
  `LIMIT :limit` works.
- Never interpolate values into SQL. The only interpolation allowed is of
  trusted constants (whitelisted `ORDER BY` clauses, table names from a
  hard-coded list, `IN (?, ?, ?)` placeholder lists).
- Escape `%`, `_` and `\` in user text used with `LIKE` (see `IdeaRepository::where()`).
- Multi-statement writes go in `$this->db->transaction(fn (PDO $pdo) => ...)`
  and pass `$pdo` to `run()`.
- The schema has no foreign keys. Deleting an idea or author must also delete
  its rows in `IdeaCategory`, `IdeaDepartment`, `Comment` and `Vote` (see
  `IdeaRepository::deleteIdeas()`).
- Keep the legacy table and column names exactly (`Author_ID`, `IdeaText`,
  `VoteNumber`, `Time`, ...). Production is case-sensitive.
- Normalise row types in the repository (`(int)` IDs and counts) so templates
  can rely on them.

## Schema changes
- Never edit `database/schema.sql` to change the schema. It is the legacy
  baseline. Add a migration instead (see the `add-migration` skill).
- Migrations must be idempotent and must work against the unknown,
  hand-made production schema: inspect `information_schema` before altering,
  preserve nullability and data, and never drop data.
- Migrations run automatically on every container start, so a failing
  migration is logged but does not stop the site.
