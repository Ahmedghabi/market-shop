<?php

namespace App\Enum;

enum StockMovementType: string
{
    case In = 'IN';
    case Out = 'OUT';
    case Adjustment = 'ADJUSTMENT';
    case Reservation = 'RESERVATION';
}
