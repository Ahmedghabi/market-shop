<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Loyalty\LoyaltyGrantInput;
use App\Dto\Loyalty\LoyaltyGrantOutput;
use App\Entity\Customer;
use App\Repository\BoutiqueRepository;
use App\Repository\CustomerRepository;
use App\Security\BoutiqueContext;
use App\Service\Loyalty\LoyaltyEngine;
use App\State\Common\BoutiqueWriteResolverTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<LoyaltyGrantOutput> */
final readonly class LoyaltyGrantProcessor implements ProcessorInterface
{
    use BoutiqueWriteResolverTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
        private CustomerRepository $customers,
        private LoyaltyEngine $engine,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): LoyaltyGrantOutput
    {
        assert($data instanceof LoyaltyGrantInput);
        $boutique = $this->resolveBoutiqueForWrite($data, $uriVariables, $context);

        $customer = $this->customers->find($data->customerId);
        if (!$customer instanceof Customer || (string) $customer->getBoutique()->getId() !== (string) $boutique->getId()) {
            throw new NotFoundHttpException('Client introuvable pour cette boutique');
        }

        $account = $this->engine->manualAdjustment($customer, $boutique, $data->points, $data->reason);

        $output = new LoyaltyGrantOutput();
        $output->customerId = (string) $customer->getId();
        $output->boutiqueId = (string) $boutique->getId();
        $output->pointsBalance = $account->getPointsBalance();
        $output->totalEarned = $account->getTotalEarned();
        $output->totalUsed = $account->getTotalUsed();

        return $output;
    }
}
