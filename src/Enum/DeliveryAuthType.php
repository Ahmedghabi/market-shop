<?php

namespace App\Enum;

enum DeliveryAuthType: string
{
    case None = 'none';
    case Basic = 'basic';
    case Bearer = 'bearer';
    case ApiKey = 'api_key';
    case Custom = 'custom';
}
