<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Application\UseCase;

use Edge\NotificationCleanArch\Application\Port\ClockPort;
use Edge\NotificationCleanArch\Application\Port\ConfigPort;
use Edge\NotificationCleanArch\Application\Port\NotificationRepositoryPort;

final readonly class PruneOldNotifications
{
    public function __construct(
        private NotificationRepositoryPort $repo,
        private ConfigPort $config,
        private ClockPort $clock,
        private int $defaultDays = 90,
    ) {}

    public function execute(): int
    {
        $enabled = $this->config->getString('auto_delete_notifications', '0') === '1';
        if (! $enabled) {
            return 0;
        }

        $days = (int) $this->config->getString('auto_delete_notifications_days', (string) $this->defaultDays);
        if ($days < 1) {
            $days = $this->defaultDays;
        }

        $cutoff = $this->clock->now()->modify("-{$days} days");
        $prunable = $this->repo->findPrunableNotifications($cutoff);

        foreach ($prunable as $notification) {
            $this->repo->deleteNotification($notification->id);
        }

        return count($prunable);
    }
}

