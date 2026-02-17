<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Application\Port;

use Edge\NotificationCleanArch\Domain\Enum\Channel;
use Edge\NotificationCleanArch\Domain\Entity\Recipient;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;

interface DriverPort
{
    /**
     * @param array<string, mixed> $context
     */
    public function send(Channel $channel, Recipient $recipient, NotificationPayload $payload, array $context = []): bool;
}

