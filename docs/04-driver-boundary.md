## Driver boundary

Drivers are IO adapters. They should not know:

- where recipients came from
- how notifications are persisted
- how status rollups work

They only do: `send(to, title, message, data)` and return a result.

### Typical drivers (mirrors backend)

- database
- email
- sms
- fcm

### Mapping to backend

- `Modules/Notification/App/Services/Drivers/DatabaseDriver.php`
- `Modules/Notification/App/Services/Drivers/EmailDriver.php`
- `Modules/Notification/App/Services/Drivers/SmsDriver.php`
- `Modules/Notification/App/Services/Drivers/FcmDriver.php`

