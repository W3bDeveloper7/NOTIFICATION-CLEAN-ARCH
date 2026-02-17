<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Domain\Entity;

use Edge\NotificationCleanArch\Domain\Enum\Channel;
use Edge\NotificationCleanArch\Domain\Enum\DeliveryStatus;

final class NotificationRecipientRow
{
    public function __construct(
        public int $id,
        public int $notificationId,
        public int $recipientId,
        public string $recipientType,
        public Channel $channel,
        public DeliveryStatus $status,
        public ?\DateTimeImmutable $sentAt = null,
        public ?\DateTimeImmutable $readAt = null,
    ) {}
}

