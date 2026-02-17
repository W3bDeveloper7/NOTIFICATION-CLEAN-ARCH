<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Application\Port;

use Edge\NotificationCleanArch\Domain\Entity\NotificationAggregate;
use Edge\NotificationCleanArch\Domain\Entity\NotificationRecipientRow;
use Edge\NotificationCleanArch\Domain\Entity\Recipient;
use Edge\NotificationCleanArch\Domain\Enum\Channel;
use Edge\NotificationCleanArch\Domain\Enum\DeliveryStatus;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;

interface NotificationRepositoryPort
{
    public function createNotification(NotificationPayload $payload, bool $isBroadcast, int $recipientsCount): NotificationAggregate;

    public function createRecipientRow(int $notificationId, Recipient $recipient, Channel $channel): NotificationRecipientRow;

    /**
     * @param list<NotificationRecipientRow> $rows
     */
    public function bulkInsertRecipientRows(int $notificationId, array $rows): void;

    public function setRecipientStatus(int $recipientRowId, DeliveryStatus $status, ?\DateTimeImmutable $sentAt = null): void;

    /**
     * Convenience for broadcast scenarios where a job only knows the identity tuple.
     */
    public function setRecipientStatusByIdentity(
        int $notificationId,
        int $recipientId,
        string $recipientType,
        Channel $channel,
        DeliveryStatus $status,
        ?\DateTimeImmutable $sentAt = null
    ): void;

    /**
     * Roll up overall notification status from recipient rows.
     * - sent if all sent
     * - failed if all failed
     * - pending otherwise
     */
    public function rollupNotificationStatus(int $notificationId): void;

    /**
     * @return list<NotificationAggregate>
     */
    public function findPrunableNotifications(\DateTimeImmutable $cutoff): array;

    public function deleteNotification(int $notificationId): void;
}

