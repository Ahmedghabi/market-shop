<?php

namespace App\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class MessengerClearEntityManagerSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [];
    }
}
