<?php

namespace App\Enum;

enum DeliveryResponseType: string
{
    case Json = 'json';
    case Xml = 'xml';
    case Text = 'text';
}
