<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Edge\NotificationCleanArch\Application\UseCase\SendBroadcastChunked;
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

$payload = new NotificationPayload(
    title: 'Broadcast',
    message: 'To many users',
    channels: [Channel::Database, Channel::Email]
);

$all = [];
for ($i = 1; $i <= 1200; $i++) {
    $all[] = new Recipient(id: $i, type: 'user', email: "u{$i}@example.com");
}

$chunksFactory = function (int $chunkSize) use ($all): iterable {
    for ($i = 0; $i < count($all); $i += $chunkSize) {
        yield array_slice($all, $i, $chunkSize);
    }
};

$uc = new SendBroadcastChunked($repo, $queue, $driver, $clock, chunkSize: 500);
$notificationId = $uc->execute(recipientsCount: count($all), recipientChunksFactory: $chunksFactory, payload: $payload);

echo "Created broadcast notification: {$notificationId}\n";
echo "Queued jobs: ".$queue->count()."\n";

$queue->runAll();
$n = $repo->getNotification($notificationId);
echo "Final status: ".$n?->status->value."\n";

