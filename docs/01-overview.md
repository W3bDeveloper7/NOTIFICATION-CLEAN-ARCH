## Overview

This package is a clean-architecture reference for `backend/Modules/Notification`.

### Layers

- **Domain**: concepts only (no IO)
- **Application**: use-cases and ports (interfaces)
- **Infrastructure**: concrete adapters (drivers, repositories, queue)
- **Presentation**: shaping outputs (resources/DTOs)

### Flow (send to user)

```mermaid
sequenceDiagram
  participant Controller
  participant NotificationService
  participant NotificationRepo
  participant Queue
  participant Driver

  Controller->>NotificationService: sendToUser(user,payload)
  NotificationService->>NotificationRepo: createNotification(...)
  loop each channel
    NotificationService->>NotificationRepo: createRecipientRow(...)
    NotificationService->>Queue: dispatch(SendNotificationJob)
  end
  Queue->>Driver: send(to,title,message,data)
  Driver-->>Queue: result(success/fail)
  Queue->>NotificationRepo: updateRecipientStatus(...)
  Queue->>NotificationRepo: rollupNotificationStatus(...)
```

### Flow (broadcast chunked)

```mermaid
flowchart TD
  startNode[Start] --> enabledCheck{AutoDeleteEnabled?}
  enabledCheck -->|No| stopNode[DoNothing]
  enabledCheck -->|Yes| buildQuery[BuildRecipientQuery]
  buildQuery --> createNotif[CreateSingleBroadcastNotification]
  createNotif --> chunkLoop[ChunkRecipientsById]
  chunkLoop --> bulkInsert[BulkInsertRecipientRows]
  bulkInsert --> dispatchJobs[DispatchJobsPerRecipientPerChannel]
  dispatchJobs --> endNode[Done]
```

### Mapping to real code (backend)

- Orchestration: `Modules/Notification/App/Services/NotificationService.php`
- Job execution + status rollup: `Modules/Notification/App/Jobs/SendNotificationJob.php`
- Laravel Notification channel bridge: `Modules/Notification/App/Channels/ModulesNotificationChannel.php`
- Pruning decision + image cleanup: `Modules/Notification/App/Models/Notification.php`

