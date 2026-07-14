<?php

namespace App\MessageHandler;

use App\Entity\BoutiqueDeliveryAccount;
use App\Entity\Order;
use App\Message\CreateShipmentMessage;
use App\Service\Delivery\DeliveryEngine;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateShipmentMessageHandler
{
    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly DeliveryEngine $engine,
    ) {
    }

    public function __invoke(CreateShipmentMessage $message): void
    {
        $order = $this->em->find(Order::class, $message->getOrderId());
        if (!$order instanceof Order) {
            return;
        }

        $account = null;
        if (null !== $message->getAccountId()) {
            $account = $this->em->find(BoutiqueDeliveryAccount::class, $message->getAccountId());
        }

        $this->engine->createShipmentForOrder($order, $account instanceof BoutiqueDeliveryAccount ? $account : null);
    }
}
