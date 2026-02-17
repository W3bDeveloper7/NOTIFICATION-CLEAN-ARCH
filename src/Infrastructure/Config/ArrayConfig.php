<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Infrastructure\Config;

use Edge\NotificationCleanArch\Application\Port\ConfigPort;

final readonly class ArrayConfig implements ConfigPort
{
    /**
     * @param array<string, string> $values
     */
    public function __construct(
        private array $values
    ) {}

    public function getString(string $key, string $default): string
    {
        $value = $this->values[$key] ?? null;
        return is_string($value) && $value !== '' ? $value : $default;
    }
}

