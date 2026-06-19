<?php

namespace App\Event;

use App\Entity\Message;

final class MessageSentEvent
{
    public function __construct(
        private readonly Message $message,
    ) {
    }

    public function getMessage(): Message
    {
        return $this->message;
    }
}
