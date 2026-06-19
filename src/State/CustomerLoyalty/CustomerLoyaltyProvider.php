<?php

namespace App\State\CustomerLoyalty;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\CustomerLoyalty\CustomerLoyaltyOutput;
use App\Entity\CustomerLoyalty;
use App\Entity\User;
use App\Repository\CustomerLoyaltyRepository;
use App\Repository\CustomerRepository;
use Symfony\Bundle\SecurityBundle\Security;

final class CustomerLoyaltyProvider implements ProviderInterface
{
    public function __construct(
        private readonly CustomerLoyaltyRepository $repository,
        private readonly CustomerRepository $customers,
        private readonly Security $security,
    ) {
    }

    /** @return array<CustomerLoyaltyOutput>|CustomerLoyaltyOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|CustomerLoyaltyOutput|null
    {
        $user = $this->security->getUser();
        if (!$user instanceof User) {
            return null;
        }

        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);

            return $entity ? $this->toOutput($entity) : null;
        }

        $customers = $this->customers->findBy(['user' => $user]);

        $result = [];
        foreach ($customers as $customer) {
            $loyalties = $this->repository->findBy(['customer' => $customer]);
            foreach ($loyalties as $loyalty) {
                $result[] = $this->toOutput($loyalty);
            }
        }

        return $result;
    }

    private function toOutput(CustomerLoyalty $entity): CustomerLoyaltyOutput
    {
        $output = new CustomerLoyaltyOutput();
        $output->id = (string) $entity->getId();
        $output->customerId = (string) $entity->getCustomer()->getId();
        $output->boutiqueId = (string) $entity->getBoutique()->getId();
        $output->pointsBalance = $entity->getPointsBalance();
        $output->totalEarned = $entity->getTotalEarned();
        $output->totalUsed = $entity->getTotalUsed();

        return $output;
    }
}
