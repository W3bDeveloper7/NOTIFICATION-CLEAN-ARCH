<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Tests;

use Edge\NotificationCleanArch\Application\UseCase\PruneOldNotifications;
use Edge\NotificationCleanArch\Domain\Enum\Channel;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;
use Edge\NotificationCleanArch\Infrastructure\Clock\SystemClock;
use Edge\NotificationCleanArch\Infrastructure\Config\ArrayConfig;
use Edge\NotificationCleanArch\Infrastructure\Persistence\InMemoryNotificationRepository;
use PHPUnit\Framework\TestCase;

final class PruneOldNotificationsTest extends TestCase
{
    public function test_it_does_not_prune_when_disabled(): void
    {
        $repo = new InMemoryNotificationRepository();
        $clock = new SystemClock();
        $config = new ArrayConfig([
            'auto_delete_notifications' => '0',
            'auto_delete_notifications_days' => '90',
        ]);

        $payload = new NotificationPayload('Old', 'Prunable', [Channel::Database]);
        $n = $repo->createNotification($payload, isBroadcast: false, recipientsCount: 1);
        $n->createdAt = $clock->now()->modify('-100 days');

        $uc = new PruneOldNotifications($repo, $config, $clock);
        self::assertSame(0, $uc->execute());
        self::assertNotNull($repo->getNotification($n->id));
    }

    public function test_it_prunes_when_enabled_and_older_than_cutoff(): void
    {
        $repo = new InMemoryNotificationRepository();
        $clock = new SystemClock();
        $config = new ArrayConfig([
            'auto_delete_notifications' => '1',
            'auto_delete_notifications_days' => '90',
        ]);

        $payload = new NotificationPayload('Old', 'Prunable', [Channel::Database]);
        $n = $repo->createNotification($payload, isBroadcast: false, recipientsCount: 1);
        $n->createdAt = $clock->now()->modify('-100 days');

        $uc = new PruneOldNotifications($repo, $config, $clock);
        self::assertSame(1, $uc->execute());
        self::assertNull($repo->getNotification($n->id));
    }

    public function test_days_is_safely_defaulted_when_invalid(): void
    {
        $repo = new InMemoryNotificationRepository();
        $clock = new SystemClock();
        $config = new ArrayConfig([
            'auto_delete_notifications' => '1',
            'auto_delete_notifications_days' => '0',
        ]);

        $payload = new NotificationPayload('Old', 'Prunable', [Channel::Database]);
        $n = $repo->createNotification($payload, isBroadcast: false, recipientsCount: 1);
        $n->createdAt = $clock->now()->modify('-100 days');

        $uc = new PruneOldNotifications($repo, $config, $clock, defaultDays: 90);
        self::assertSame(1, $uc->execute());
        self::assertNull($repo->getNotification($n->id));
    }
}

