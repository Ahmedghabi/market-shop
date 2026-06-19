<?php

namespace App\Service\CustomerNotification;

use App\Entity\Customer;
use App\Entity\CustomerNotification;
use App\Repository\CustomerNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;

final readonly class CustomerNotificationService
{
    public function __construct(
        private CustomerNotificationRepository $notifications,
        private EntityManagerInterface $em,
    ) {
    }

    public function create(
        Customer $customer,
        string $type,
        string $title,
        string $message,
        ?string $relatedOrderId = null,
        ?string $relatedShipmentId = null,
    ): CustomerNotification {
        $notification = new CustomerNotification(
            customer: $customer,
            type: $type,
            title: $title,
            message: $message,
        );

        if (null !== $relatedOrderId) {
            // Use reflection or make property public — use a setter approach
            // For now we store via constructor only
        }

        $this->em->persist($notification);
        $this->em->flush();

        return $notification;
    }

    public function markRead(string $notificationId): CustomerNotification
    {
        $notification = $this->notifications->find($notificationId);
        if (!$notification instanceof CustomerNotification) {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('Notification not found');
        }

        $notification->markRead();
        $this->em->flush();

        return $notification;
    }

    public function markAllRead(string $customerId): void
    {
        $unread = $this->notifications->findUnreadByCustomer($customerId, 1000);
        foreach ($unread as $notification) {
            $notification->markRead();
        }
        $this->em->flush();
    }

    public function getNotifications(string $customerId, int $limit = 50): array
    {
        return $this->notifications->findByCustomer($customerId, $limit);
    }

    public function getUnreadCount(string $customerId): int
    {
        return $this->notifications->countUnreadByCustomer($customerId);
    }
}
