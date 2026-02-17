<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Tests;

use Edge\NotificationCleanArch\Application\UseCase\SendToUser;
use Edge\NotificationCleanArch\Domain\Entity\Recipient;
use Edge\NotificationCleanArch\Domain\Enum\Channel;
use Edge\NotificationCleanArch\Domain\Enum\DeliveryStatus;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;
use Edge\NotificationCleanArch\Infrastructure\Clock\SystemClock;
use Edge\NotificationCleanArch\Infrastructure\Driver\FakeDriver;
use Edge\NotificationCleanArch\Infrastructure\Persistence\InMemoryNotificationRepository;
use Edge\NotificationCleanArch\Infrastructure\Queue\InMemoryQueue;
use PHPUnit\Framework\TestCase;

final class RollupStatusTest extends TestCase
{
    public function test_single_user_rolls_up_to_sent_when_all_channels_succeed(): void
    {
        $repo = new InMemoryNotificationRepository();
        $queue = new InMemoryQueue();
        $driver = new FakeDriver();
        $clock = new SystemClock();

        $uc = new SendToUser($repo, $queue, $driver, $clock);

        $user = new Recipient(id: 1, type: 'user', email: 'a@b.com', phone: '1', fcmTokens: ['t']);
        $payload = new NotificationPayload('T', 'M', [Channel::Database, Channel::Email, Channel::Sms, Channel::Fcm]);

        $notificationId = $uc->execute($user, $payload);
        $queue->runAll();

        $n = $repo->getNotification($notificationId);
        self::assertSame(DeliveryStatus::Sent, $n?->status);
    }

    public function test_single_user_rolls_up_to_pending_when_mixed_results(): void
    {
        $repo = new InMemoryNotificationRepository();
        $queue = new InMemoryQueue();
        $driver = new FakeDriver([
            'email:1' => false,
        ]);
        $clock = new SystemClock();

        $uc = new SendToUser($repo, $queue, $driver, $clock);

        $user = new Recipient(id: 1, type: 'user', email: 'a@b.com');
        $payload = new NotificationPayload('T', 'M', [Channel::Database, Channel::Email]);

        $notificationId = $uc->execute($user, $payload);
        $queue->runAll();

        $n = $repo->getNotification($notificationId);
        self::assertSame(DeliveryStatus::Pending, $n?->status);
    }
}

