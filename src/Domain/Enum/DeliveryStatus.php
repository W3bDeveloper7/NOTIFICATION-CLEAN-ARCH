<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Domain\Enum;

enum DeliveryStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case Failed = 'failed';
}

