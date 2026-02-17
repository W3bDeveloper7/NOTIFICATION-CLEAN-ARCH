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

// This example focuses on the idea: build a deduped recipient query, then broadcast chunked.
// In the real app, recipients come from user_ids + groups + companies.

$repo = new InMemoryNotificationRepository();
$queue = new InMemoryQueue();
$driver = new FakeDriver();
$clock = new SystemClock();

$payload = new NotificationPayload(
    title: 'Segment send',
    message: 'users + companies + groups',
    channels: [Channel::Database]
);

// Pretend these are resolved from segments (already deduped).
$recipients = [
    new Recipient(id: 1, type: 'user'),
    new Recipient(id: 2, type: 'user'),
    new Recipient(id: 3, type: 'user'),
];

$chunksFactory = fn (int $chunkSize): iterable => [$recipients];

$uc = new SendBroadcastChunked($repo, $queue, $driver, $clock, chunkSize: 500);
$notificationId = $uc->execute(count($recipients), $chunksFactory, $payload);

echo "Created segment broadcast notification: {$notificationId}\n";
echo "Queued jobs: ".$queue->count()."\n";
$queue->runAll();
$n = $repo->getNotification($notificationId);
echo "Final status: ".$n?->status->value."\n";

