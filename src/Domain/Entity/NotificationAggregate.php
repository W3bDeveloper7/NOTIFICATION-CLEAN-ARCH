<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Domain\Entity;

use Edge\NotificationCleanArch\Domain\Enum\DeliveryStatus;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;

final class NotificationAggregate
{
    /**
     * @param  array<string, mixed>  $meta
     */
    public function __construct(
        public int $id,
        public bool $isBroadcast,
        public int $recipientsCount,
        public DeliveryStatus $status,
        public \DateTimeImmutable $createdAt,
        public NotificationPayload $payload,
        public array $meta = [],
    ) {}
}

