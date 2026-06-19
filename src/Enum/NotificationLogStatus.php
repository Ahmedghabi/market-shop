<?php

namespace App\Enum;

enum NotificationLogStatus: string
{
    case Pending = 'PENDING';
    case Sent = 'SENT';
    case Failed = 'FAILED';
}
