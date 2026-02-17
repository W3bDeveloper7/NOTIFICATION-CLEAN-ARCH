<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Infrastructure\Driver;

use Edge\NotificationCleanArch\Application\Port\DriverPort;
use Edge\NotificationCleanArch\Domain\Enum\Channel;
use Edge\NotificationCleanArch\Domain\Entity\Recipient;
use Edge\NotificationCleanArch\Domain\ValueObject\NotificationPayload;

final readonly class FakeDriver implements DriverPort
{
    /**
     * @param array<string, bool> $forcedResults Keyed by "{$channel}:{$recipientId}"
     */
    public function __construct(
        private array $forcedResults = []
    ) {}

    public function send(Channel $channel, Recipient $recipient, NotificationPayload $payload, array $context = []): bool
    {
        $key = $channel->value.':'.$recipient->id;

        if (array_key_exists($key, $this->forcedResults)) {
            return (bool) $this->forcedResults[$key];
        }

        // Default behavior: succeed if the recipient has an address for the channel.
        return match ($channel) {
            Channel::Database => true,
            Channel::Email => is_string($recipient->email) && $recipient->email !== '',
            Channel::Sms => is_string($recipient->phone) && $recipient->phone !== '',
            Channel::Fcm => $recipient->fcmTokens !== [],
        };
    }
}

