<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Domain\ValueObject;

use Edge\NotificationCleanArch\Domain\Enum\Channel;

final readonly class NotificationPayload
{
    /**
     * @param  list<Channel>  $channels
     * @param  array<string, mixed>  $data
     */
    public function __construct(
        public string $title,
        public string $message,
        public array $channels = [Channel::Database],
        public array $data = [],
        public ?string $type = null,
        public ?string $target = null,
        public int $priority = 1,
    ) {
        $this->assertValid();
    }

    private function assertValid(): void
    {
        if (trim($this->title) === '' || trim($this->message) === '') {
            throw new \InvalidArgumentException('title and message are required');
        }
        if ($this->priority < 1) {
            throw new \InvalidArgumentException('priority must be >= 1');
        }
        if ($this->channels === []) {
            throw new \InvalidArgumentException('at least one channel is required');
        }
    }
}

