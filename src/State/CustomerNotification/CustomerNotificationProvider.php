<?php

namespace App\State\CustomerNotification;

use App\Dto\CustomerNotification\CustomerNotificationOutput;
use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\CustomerNotification\CustomerNotificationService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class CustomerNotificationProvider implements ProviderInterface
{
    public function __construct(
        private CustomerNotificationService $notificationService,
        private CustomerRepository $customers,
        private Security $security,
    ) {
    }

    /** @return list<CustomerNotificationOutput> */
    public function getCollection(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $user = $this->security->getUser();
        if (!$user instanceof \App\Entity\User) {
            return [];
        }

        $customer = $this->customers->findOneBy(['user' => $user]);
        if (!$customer instanceof Customer) {
            return [];
        }

        $notifications = $this->notificationService->getNotifications($customer->getId());

        return array_map(
            fn (\App\Entity\CustomerNotification $n) => CustomerNotificationOutput::fromEntity($n),
            $notifications,
        );
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?CustomerNotificationOutput
    {
        $user = $this->security->getUser();
        if (!$user instanceof \App\Entity\User) {
            return null;
        }

        $customer = $this->customers->findOneBy(['user' => $user]);
        if (!$customer instanceof Customer) {
            return null;
        }

        $id = $uriVariables['id'] ?? null;
        if (null === $id) {
            return null;
        }

        $notifications = $this->notificationService->getNotifications($customer->getId(), 1000);
        foreach ($notifications as $notification) {
            if ($notification->getId() === $id) {
                return CustomerNotificationOutput::fromEntity($notification);
            }
        }

        return null;
    }
}
