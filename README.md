## Notification Clean Architecture (Reference Package)

This is a **standalone Composer package** that documents and demonstrates (with executable stubs) the architecture of the Laravel module:

- `edge-backend/Modules/Notification`

It is intentionally **framework-light**: the goal is clarity of boundaries and flows rather than a full Laravel app.

### What this package demonstrates

- **Domain**: notification concepts (payload, channels, statuses)
- **Application**: use-cases (send to user, broadcast with chunking, segments), driver boundary, status rollup
- **Infrastructure**: in-memory persistence, fake queue, fake drivers
- **Presentation**: HTTP-like DTO shaping (resources)
- **Pruning policy**: config-driven deletion decision (default disabled, 90 days)

### Mapping to the real codebase

Key files in `edge-backend` that inspired this package:

- Service orchestration: `Modules/Notification/App/Services/NotificationService.php`
- Job boundary: `Modules/Notification/App/Jobs/SendNotificationJob.php`
- Channel bridge: `Modules/Notification/App/Channels/ModulesNotificationChannel.php`
- DB model + pruning: `Modules/Notification/App/Models/Notification.php`

### Quick start

From `c:\laragon\www\notification-clean-arch`:

```bash
composer install
composer test
```

Examples:

```bash
php examples/01_send_to_user.php
php examples/02_broadcast_chunked.php
php examples/03_send_to_segments.php
php examples/04_prune_policy.php
```

