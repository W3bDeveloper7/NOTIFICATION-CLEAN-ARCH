<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Application\Port;

interface ConfigPort
{
    public function getString(string $key, string $default): string;
}

