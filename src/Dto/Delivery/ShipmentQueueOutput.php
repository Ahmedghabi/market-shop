<?php

namespace App\Dto\Delivery;

final class ShipmentQueueOutput
{
    public string $status = 'queued';
    public string $orderId;
}
