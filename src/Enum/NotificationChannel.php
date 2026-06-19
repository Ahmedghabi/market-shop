<?php

namespace App\Enum;

enum NotificationChannel: string
{
    case Email = 'EMAIL';
    case Sms = 'SMS';
    case Whatsapp = 'WHATSAPP';
    case Internal = 'INTERNAL';
}
