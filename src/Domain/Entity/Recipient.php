<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Domain\Entity;

final readonly class Recipient
{
    public function __construct(
        public int $id,
        public string $type,
        public ?string $email = null,
        public ?string $phone = null,
        /** @var list<string> */
        public array $fcmTokens = [],
        public ?string $locale = null,
    ) {}
}

