<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Infrastructure\Persistence;

use Edge\NotificationCleanArch\Application\Port\NotificationRepositoryPort;
use Edge\NotificationCleanArch\Domain\Entity\NotificationAggregate;
use Edge\NotificationCleanArch\Domain\Entity\NotificationRecipientRow;
use Edge\NotificationCleanArch\Domain\Entity\Recipient;
use Edge\NotificationCleanArch\Domain\Enum\Channel;
use Edge\NotificationCleanArch\Domain\Enum\DeliveryStatus;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;

final class InMemoryNotificationRepository implements NotificationRepositoryPort
{
    /** @var array<int, NotificationAggregate> */
    private array $notifications = [];

    /** @var array<int, NotificationRecipientRow> */
    private array $recipientRows = [];

    private int $nextNotificationId = 1;

    private int $nextRecipientRowId = 1;

    public function createNotification(NotificationPayload $payload, bool $isBroadcast, int $recipientsCount): NotificationAggregate
    {
        $id = $this->nextNotificationId++;
        $notification = new NotificationAggregate(
            id: $id,
            isBroadcast: $isBroadcast,
            recipientsCount: $recipientsCount,
            status: DeliveryStatus::Pending,
            createdAt: new \DateTimeImmutable('now'),
            payload: $payload,
            meta: []
        );

        $this->notifications[$id] = $notification;

        return $notification;
    }

    public function createRecipientRow(int $notificationId, Recipient $recipient, Channel $channel): NotificationRecipientRow
    {
        $id = $this->nextRecipientRowId++;
        $row = new NotificationRecipientRow(
            id: $id,
            notificationId: $notificationId,
            recipientId: $recipient->id,
            recipientType: $recipient->type,
            channel: $channel,
            status: DeliveryStatus::Pending
        );

        $this->recipientRows[$id] = $row;

        return $row;
    }

    public function bulkInsertRecipientRows(int $notificationId, array $rows): void
    {
        foreach ($rows as $row) {
            $id = $this->nextRecipientRowId++;
            $this->recipientRows[$id] = new NotificationRecipientRow(
                id: $id,
                notificationId: $notificationId,
                recipientId: $row->recipientId,
                recipientType: $row->recipientType,
                channel: $row->channel,
                status: $row->status,
                sentAt: $row->sentAt,
                readAt: $row->readAt
            );
        }
    }

    public function setRecipientStatus(int $recipientRowId, DeliveryStatus $status, ?\DateTimeImmutable $sentAt = null): void
    {
        $row = $this->recipientRows[$recipientRowId] ?? null;
        if (! $row) {
            return;
        }

        $row->status = $status;
        $row->sentAt = $sentAt;
    }

    public function setRecipientStatusByIdentity(
        int $notificationId,
        int $recipientId,
        string $recipientType,
        Channel $channel,
        DeliveryStatus $status,
        ?\DateTimeImmutable $sentAt = null
    ): void {
        foreach ($this->recipientRows as $row) {
            if (
                $row->notificationId === $notificationId
                && $row->recipientId === $recipientId
                && $row->recipientType === $recipientType
                && $row->channel === $channel
            ) {
                $row->status = $status;
                $row->sentAt = $sentAt;

                return;
            }
        }
    }

    public function rollupNotificationStatus(int $notificationId): void
    {
        $notification = $this->notifications[$notificationId] ?? null;
        if (! $notification) {
            return;
        }

        $rows = array_values(array_filter(
            $this->recipientRows,
            fn (NotificationRecipientRow $r) => $r->notificationId === $notificationId
        ));

        if ($rows === []) {
            return;
        }

        $sent = 0;
        $failed = 0;
        $pending = 0;

        foreach ($rows as $r) {
            match ($r->status) {
                DeliveryStatus::Sent => $sent++,
                DeliveryStatus::Failed => $failed++,
                DeliveryStatus::Pending => $pending++,
            };
        }

        $total = $sent + $failed + $pending;

        if ($pending === 0 && $failed === 0 && $sent === $total) {
            $notification->status = DeliveryStatus::Sent;
            return;
        }

        if ($pending === 0 && $sent === 0 && $failed === $total) {
            $notification->status = DeliveryStatus::Failed;
            return;
        }

        $notification->status = DeliveryStatus::Pending;
    }

    public function findPrunableNotifications(\DateTimeImmutable $cutoff): array
    {
        return array_values(array_filter(
            $this->notifications,
            fn (NotificationAggregate $n) => $n->createdAt < $cutoff
        ));
    }

    public function deleteNotification(int $notificationId): void
    {
        unset($this->notifications[$notificationId]);

        foreach ($this->recipientRows as $id => $row) {
            if ($row->notificationId === $notificationId) {
                unset($this->recipientRows[$id]);
            }
        }
    }

    // Helpers for examples/tests
    public function getNotification(int $id): ?NotificationAggregate
    {
        return $this->notifications[$id] ?? null;
    }
}

