<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Edge\NotificationCleanArch\Application\UseCase\SendToUser;
use Edge\NotificationCleanArch\Domain\Entity\Recipient;
use Edge\NotificationCleanArch\Domain\Enum\Channel;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;
use Edge\NotificationCleanArch\Infrastructure\Clock\SystemClock;
use Edge\NotificationCleanArch\Infrastructure\Driver\FakeDriver;
use Edge\NotificationCleanArch\Infrastructure\Persistence\InMemoryNotificationRepository;
use Edge\NotificationCleanArch\Infrastructure\Queue\InMemoryQueue;

$repo = new InMemoryNotificationRepository();
$queue = new InMemoryQueue();
$driver = new FakeDriver();
$clock = new SystemClock();

$uc = new SendToUser($repo, $queue, $driver, $clock);

$user = new Recipient(id: 10, type: 'user', email: 'user@example.com', phone: '123', fcmTokens: ['t1']);
$payload = new NotificationPayload(
    title: 'Hello',
    message: 'Welcome',
    channels: [Channel::Database, Channel::Email, Channel::Fcm]
);

$notificationId = $uc->execute($user, $payload);

echo "Created notification: {$notificationId}\n";
echo "Queued jobs: ".$queue->count()."\n";

$queue->runAll();

$n = $repo->getNotification($notificationId);
echo "Final status: ".$n?->status->value."\n";

