<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Application\Port;

interface QueuePort
{
    /**
     * @param callable(): void $job
     */
    public function dispatch(callable $job): void;
}

