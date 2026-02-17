<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Infrastructure\Clock;

use Edge\NotificationCleanArch\Application\Port\ClockPort;

final class SystemClock implements ClockPort
{
    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now');
    }
}

