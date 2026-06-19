<?php

namespace App\Enum;

enum CartStatus: string
{
    case Active = 'active';
    case Ordered = 'ordered';
    case Abandoned = 'abandoned';
}
