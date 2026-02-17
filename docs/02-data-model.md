## Data model

This mirrors the intent of:

- `system_notifications`
- `system_notification_users`
- `system_notification_translations`

### Tables

```mermaid
erDiagram
  SYSTEM_NOTIFICATIONS {
    bigint id PK
    bigint topic_id
    bigint user_id
    string user_type
    string type
    string target
    int priority
    json channel
    json data
    string status
    boolean is_broadcast
    int recipients_count
    datetime created_at
  }

  SYSTEM_NOTIFICATION_USERS {
    bigint id PK
    bigint system_notification_id FK
    bigint user_id
    string user_type
    string channel
    string status
    datetime read_at
    datetime sent_at
  }

  SYSTEM_NOTIFICATION_TRANSLATIONS {
    bigint id PK
    bigint system_notification_id FK
    string locale
    string title
    text message
  }

  SYSTEM_NOTIFICATIONS ||--o{ SYSTEM_NOTIFICATION_USERS : hasMany
  SYSTEM_NOTIFICATIONS ||--o{ SYSTEM_NOTIFICATION_TRANSLATIONS : hasMany
```

### Important invariants

- One broadcast notification row for an audience, and N recipient rows (per user x channel).
- Recipient rows are unique per `(notification_id, user_id, user_type, channel)`.\n
