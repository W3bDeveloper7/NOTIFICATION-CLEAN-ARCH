## Send flows

### Single user (multi-channel)

- Create 1 notification record
- Create 1 recipient row per channel
- Dispatch 1 job per channel

### Broadcast (10k+ recipients)

- Create 1 notification record with `is_broadcast=true` and `recipients_count`
- Process recipients in **chunks**:
  - bulk insert recipient rows
  - dispatch jobs for each (user x channel)

### Segments (user_ids + groups + companies)

Build one deduped recipient query:

- `id IN userIds`
- OR `company_id IN companyIds`
- OR `id IN (select user_id from user_user_group where user_group_id IN groupIds)`

Then broadcast using the same chunked pipeline.

### Mapping to backend

- Chunked broadcast concept: `NotificationService::sendBroadcastByQuery(...)` (refactored) and `SendNotificationJob` rollup logic.
- Segment-based send: `NotificationService::sendToUsersBySegments(...)`.

