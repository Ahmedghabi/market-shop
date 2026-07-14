<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Loyalty\LoyaltyQuoteInput;
use App\Dto\Loyalty\LoyaltyQuoteOutput;
use App\Repository\CustomerRepository;
use App\Repository\UserRepository;
use App\Service\Boutique\ShopContext;
use App\Service\Loyalty\Dto\LoyaltyRedemptionRequest;
use App\Service\Loyalty\LoyaltyEngine;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/** @implements ProcessorInterface<LoyaltyQuoteOutput> */
final readonly class LoyaltyQuoteProcessor implements ProcessorInterface
{
    use LoyaltyCustomerResolverTrait;

    public function __construct(
        private ShopContext $shopContext,
        private Security $security,
        private CustomerRepository $customers,
        private UserRepository $users,
        private LoyaltyEngine $engine,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): LoyaltyQuoteOutput
    {
        assert($data instanceof LoyaltyQuoteInput);

        $resolved = $this->resolveCurrentBoutiqueAndCustomer();
        if (null === $resolved) {
            throw new AccessDeniedHttpException('Compte client introuvable pour cette boutique');
        }
        [$boutique, $customer] = $resolved;

        $request = new LoyaltyRedemptionRequest(
            useAllPoints: $data->useAllPoints,
            pointsToUse: $data->pointsToUse,
            rewardId: $data->rewardId,
        );

        $result = $this->engine->calculateRedemption(
            $boutique,
            $customer,
            $request,
            $data->subtotalCents,
            $data->alreadyAppliedDiscountsCents,
        );

        $output = new LoyaltyQuoteOutput();
        $output->success = $result->success;
        $output->pointsUsed = $result->pointsUsed;
        $output->discountCents = $result->discountCents;
        $output->newSubtotalCents = $result->newSubtotalCents;
        $output->freeShipping = $result->freeShipping;
        $output->freeProductId = $result->freeProductId;
        $output->rewardId = $result->rewardId;
        $output->errorMessage = $result->errorMessage;

        return $output;
    }
}
