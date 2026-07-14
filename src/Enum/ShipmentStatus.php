<?php

namespace App\Enum;

enum ShipmentStatus: string
{
    case Created = 'created';
    case Sent = 'sent';
    case Accepted = 'accepted';
    case InPreparation = 'in_preparation';
    case InTransit = 'in_transit';
    case Delivered = 'delivered';
    case Failed = 'failed';
    case Cancelled = 'cancelled';
    case Return = 'return';

    public function isFinal(): bool
    {
        return in_array($this, [self::Delivered, self::Cancelled, self::Return], true);
    }
}
