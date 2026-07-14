<?php

namespace App\Message;

final readonly class SyncShipmentTrackingMessage
{
    public function __construct(
        private string $shipmentId,
    ) {
    }

    public function getShipmentId(): string
    {
        return $this->shipmentId;
    }
}
