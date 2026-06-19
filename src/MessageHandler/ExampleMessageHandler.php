<?php

namespace App\MessageHandler;

use App\Message\ExampleMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ExampleMessageHandler
{
    public function __invoke(ExampleMessage $message): void
    {
        unset($message);
    }
}
