<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Application\UseCase;

use Edge\NotificationCleanArch\Application\Port\NotificationRepositoryPort;

final readonly class RollupStatus
{
    public function __construct(
        private NotificationRepositoryPort $repo
    ) {}

    public function execute(int $notificationId): void
    {
        $this->repo->rollupNotificationStatus($notificationId);
    }
}

