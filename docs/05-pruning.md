## Pruning

The real project uses Laravel `Prunable` on the Notification model and schedules `model:prune`.

### Config keys

- `auto_delete_notifications` (default `0` disabled)
- `auto_delete_notifications_days` (default `90`)

### Behavior

- If disabled: prune query is empty (no deletions).
- If enabled: prune notifications older than `now() - days`.
- Delete associated image file if `data.image` references a stored file (storage URL).

### Mapping to backend

- Model: `Modules/Notification/App/Models/Notification.php`
- Scheduler: `app/Console/Kernel.php`
- Defaults: `Modules/Core/config/core_config.php` + seeder.

