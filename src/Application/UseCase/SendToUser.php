<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Application\UseCase;

use Edge\NotificationCleanArch\Application\Port\ClockPort;
use Edge\NotificationCleanArch\Application\Port\DriverPort;
use Edge\NotificationCleanArch\Application\Port\NotificationRepositoryPort;
use Edge\NotificationCleanArch\Application\Port\QueuePort;
use Edge\NotificationCleanArch\Domain\Entity\Recipient;
use Edge\NotificationCleanArch\Domain\Enum\Channel;
use Edge\NotificationCleanArch\Domain\Enum\DeliveryStatus;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;

final readonly class SendToUser
{
    public function __construct(
        private NotificationRepositoryPort $repo,
        private QueuePort $queue,
        private DriverPort $driver,
        private ClockPort $clock,
    ) {}

    public function execute(Recipient $recipient, NotificationPayload $payload): int
    {
        $notification = $this->repo->createNotification($payload, isBroadcast: false, recipientsCount: 1);

        foreach ($payload->channels as $channel) {
            $row = $this->repo->createRecipientRow($notification->id, $recipient, $channel);

            $this->queue->dispatch(function () use ($channel, $recipient, $payload, $notification, $row): void {
                $ok = $this->driver->send($channel, $recipient, $payload, [
                    'notification_id' => $notification->id,
                    'recipient_row_id' => $row->id,
                ]);

                $this->repo->setRecipientStatus(
                    $row->id,
                    $ok ? DeliveryStatus::Sent : DeliveryStatus::Failed,
                    $ok ? $this->clock->now() : null
                );

                $this->repo->rollupNotificationStatus($notification->id);
            });
        }

        return $notification->id;
    }
}

