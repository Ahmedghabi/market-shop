<?php

namespace App\Dto\CustomerNotification;

final class CustomerNotificationOutput
{
    public function __construct(
        public string $id,
        public string $type,
        public string $title,
        public string $message,
        public bool $isRead,
        public ?string $readAt,
        public string $createdAt,
        public int $unreadCount = 0,
    ) {
    }

    public static function fromEntity(\App\Entity\CustomerNotification $notification): self
    {
        return new self(
            id: $notification->getId(),
            type: $notification->getType(),
            title: $notification->getTitle(),
            message: $notification->getMessage(),
            isRead: $notification->isRead(),
            readAt: $notification->getReadAt()?->format('c'),
            createdAt: $notification->getCreatedAt()->format('c'),
        );
    }
}
