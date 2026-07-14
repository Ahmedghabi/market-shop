<?php

namespace App\Enum;

enum LoyaltyCostType: string
{
    case Points = 'points';
    case OrdersCount = 'orders_count';
}
