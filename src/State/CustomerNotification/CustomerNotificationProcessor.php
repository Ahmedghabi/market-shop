<?php

namespace App\State\CustomerNotification;

use App\Entity\Customer;
use App\Repository\CustomerRepository;
use App\Service\CustomerNotification\CustomerNotificationService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use Symfony\Bundle\SecurityBundle\Security;

final class CustomerNotificationProcessor implements ProcessorInterface
{
    public function __construct(
        private CustomerNotificationService $notificationService,
        private CustomerRepository $customers,
        private Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ('notification_read_all' === $operation->getName()) {
            $user = $this->security->getUser();
            if (null === $user) {
                return ['message' => 'No user found.'];
            }

            $customer = $this->customers->findOneBy(['user' => $user]);
            if (!$customer instanceof Customer) {
                return ['message' => 'No customer found.'];
            }

            $this->notificationService->markAllRead($customer->getId());

            return ['message' => 'All notifications marked as read.'];
        }

        $notificationId = $uriVariables['id'] ?? '';
        if ('' !== $notificationId) {
            return $this->notificationService->markRead($notificationId);
        }

        return null;
    }
}
