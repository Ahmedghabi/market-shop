<?php

namespace App\Enum;

enum ExtensionType: string
{
    case QuotaBoost = 'quota_boost';
    case Module = 'module';
    case Theme = 'theme';
    case Service = 'service';
}
