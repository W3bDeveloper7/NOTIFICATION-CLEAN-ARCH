<?php

declare(strict_types=1);

require __DIR__.'/../vendor/autoload.php';

use Edge\NotificationCleanArch\Application\UseCase\PruneOldNotifications;
use Edge\NotificationCleanArch\Domain\Enum\Channel;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;
use Edge\NotificationCleanArch\Infrastructure\Clock\SystemClock;
use Edge\NotificationCleanArch\Infrastructure\Config\ArrayConfig;
use Edge\NotificationCleanArch\Infrastructure\Persistence\InMemoryNotificationRepository;

$repo = new InMemoryNotificationRepository();
$clock = new SystemClock();

// Disabled by default.
$config = new ArrayConfig([
    'auto_delete_notifications' => '0',
    'auto_delete_notifications_days' => '90',
]);

// Seed a notification with an old createdAt by direct repo access (for demo).
$payload = new NotificationPayload('Old', 'Prunable', [Channel::Database]);
$n = $repo->createNotification($payload, isBroadcast: false, recipientsCount: 1);
$n->createdAt = (new DateTimeImmutable('now'))->modify('-100 days');

$uc = new PruneOldNotifications($repo, $config, $clock);
echo "Pruned (disabled): ".$uc->execute()."\n";

$configEnabled = new ArrayConfig([
    'auto_delete_notifications' => '1',
    'auto_delete_notifications_days' => '90',
]);

$uc2 = new PruneOldNotifications($repo, $configEnabled, $clock);
echo "Pruned (enabled): ".$uc2->execute()."\n";

