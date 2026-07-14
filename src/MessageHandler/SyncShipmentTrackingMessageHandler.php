<?php

namespace App\MessageHandler;

use App\Entity\Shipment;
use App\Message\SyncShipmentTrackingMessage;
use App\Service\Delivery\DeliveryEngine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SyncShipmentTrackingMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DeliveryEngine $engine,
    ) {
    }

    public function __invoke(SyncShipmentTrackingMessage $message): void
    {
        $shipment = $this->em->find(Shipment::class, $message->getShipmentId());
        if (!$shipment instanceof Shipment) {
            return;
        }

        $this->engine->trackShipment($shipment);
    }
}
