<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Application\Port;

interface ClockPort
{
    public function now(): \DateTimeImmutable;
}

