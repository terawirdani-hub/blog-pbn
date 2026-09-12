# Architecture rules

- PHP 8.0+, `declare(strict_types=1);` on every PHP file.
- No Composer packages unless the roadmap is updated.
- New operational settings: add default in `setting_defaults()`, UI on settings (or relevant) page, i18n + tooltip, audit write.
- Database changes: new migration version in `includes/schema.php`, update `docs/specs/data-model.md`.
- Do not query SQLite without `$pdo->beginTransaction()` for multi-step writes (import, user delete checks).
- Unique slugs: rely on UNIQUE constraint; on constraint error, suffix `-2`, `-3`, … and retry (bounded).
