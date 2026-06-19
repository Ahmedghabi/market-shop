<?php

namespace App\Enum;

enum RefundType: string
{
    case Full = 'FULL';
    case Partial = 'PARTIAL';
    case ItemLevel = 'ITEM_LEVEL';
}
