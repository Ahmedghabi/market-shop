<?php

namespace App\Enum;

enum SessionDeviceType: string
{
    case Mobile = 'MOBILE';
    case Tablet = 'TABLET';
    case Desktop = 'DESKTOP';
    case Unknown = 'UNKNOWN';
}
