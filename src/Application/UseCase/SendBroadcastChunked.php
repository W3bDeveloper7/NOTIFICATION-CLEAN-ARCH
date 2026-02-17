<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Application\UseCase;

use Edge\NotificationCleanArch\Application\Port\ClockPort;
use Edge\NotificationCleanArch\Application\Port\DriverPort;
use Edge\NotificationCleanArch\Application\Port\NotificationRepositoryPort;
use Edge\NotificationCleanArch\Application\Port\QueuePort;
use Edge\NotificationCleanArch\Domain\Entity\Recipient;
use Edge\NotificationCleanArch\Domain\Entity\NotificationRecipientRow;
use Edge\NotificationCleanArch\Domain\Enum\DeliveryStatus;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;

final readonly class SendBroadcastChunked
{
    /**
     * @param callable(int $chunkSize): iterable<list<Recipient>> $recipientChunksFactory
     */
    public function __construct(
        private NotificationRepositoryPort $repo,
        private QueuePort $queue,
        private DriverPort $driver,
        private ClockPort $clock,
        private int $chunkSize = 500,
    ) {}

    /**
     * @param int $recipientsCount Total recipients count (for reporting / notification row)
     * @param callable(int $chunkSize): iterable<list<Recipient>> $recipientChunksFactory
     */
    public function execute(int $recipientsCount, callable $recipientChunksFactory, NotificationPayload $payload): int
    {
        $notification = $this->repo->createNotification($payload, isBroadcast: true, recipientsCount: $recipientsCount);

        foreach ($recipientChunksFactory($this->chunkSize) as $chunk) {
            $rows = [];
            foreach ($chunk as $recipient) {
                foreach ($payload->channels as $channel) {
                    $rows[] = new NotificationRecipientRow(
                        id: 0,
                        notificationId: $notification->id,
                        recipientId: $recipient->id,
                        recipientType: $recipient->type,
                        channel: $channel,
                        status: DeliveryStatus::Pending
                    );
                }
            }

            $this->repo->bulkInsertRecipientRows($notification->id, $rows);

            // Dispatch jobs (simulated)
            foreach ($chunk as $recipient) {
                foreach ($payload->channels as $channel) {
                    $this->queue->dispatch(function () use ($channel, $recipient, $payload, $notification): void {
                        $ok = $this->driver->send($channel, $recipient, $payload, [
                            'notification_id' => $notification->id,
                            'is_broadcast' => true,
                        ]);

                        $this->repo->setRecipientStatusByIdentity(
                            notificationId: $notification->id,
                            recipientId: $recipient->id,
                            recipientType: $recipient->type,
                            channel: $channel,
                            status: $ok ? DeliveryStatus::Sent : DeliveryStatus::Failed,
                            sentAt: $ok ? $this->clock->now() : null
                        );
                        $this->repo->rollupNotificationStatus($notification->id);
                    });
                }
            }
        }

        return $notification->id;
    }
}

