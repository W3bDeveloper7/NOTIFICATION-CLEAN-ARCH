<?php

declare(strict_types=1);

namespace Edge\NotificationCleanArch\Domain\Enum;

enum Channel: string
{
    case Database = 'database';
    case Email = 'email';
    case Sms = 'sms';
    case Fcm = 'fcm';
}

