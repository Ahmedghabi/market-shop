<?php

namespace App\State\DeliveryRule;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\DeliveryRule\DeliveryRuleInput;
use App\Dto\DeliveryRule\DeliveryRuleOutput;
use App\Entity\DeliveryRule;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use App\Service\Delivery\DeliveryRuleService;
use App\State\Common\BoutiqueWriteResolverTrait;

final class DeliveryRuleProcessor implements ProcessorInterface
{
    use BoutiqueWriteResolverTrait;

    public function __construct(
        private DeliveryRuleService $ruleService,
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if (!$data instanceof DeliveryRuleInput) {
            return null;
        }

        $boutique = $this->resolveBoutiqueForWrite($data, $uriVariables, $context);

        if (isset($uriVariables['id'])) {
            $rule = $this->ruleService->getRuleById((string) $uriVariables['id']);
            if ($rule && $rule->getBoutique()->getId() === $boutique->getId()) {
                return $this->toOutput($this->ruleService->update($rule, (array) $data));
            }

            return null;
        }

        return $this->toOutput($this->ruleService->create((string) $boutique->getId(), (array) $data));
    }

    private function toOutput(DeliveryRule $rule): DeliveryRuleOutput
    {
        return new DeliveryRuleOutput(
            id: (string) $rule->getId(),
            name: $rule->getName(),
            type: $rule->getType()->value,
            priceCents: $rule->getPriceCents(),
            minWeightKg: $rule->getMinWeightKg(),
            maxWeightKg: $rule->getMaxWeightKg(),
            minDistanceKm: $rule->getMinDistanceKm(),
            maxDistanceKm: $rule->getMaxDistanceKm(),
            minCartAmountCents: $rule->getMinCartAmountCents(),
            maxCartAmountCents: $rule->getMaxCartAmountCents(),
            priority: $rule->getPriority(),
            isActive: $rule->isActive(),
            createdAt: $rule->getCreatedAt()->format('c'),
            updatedAt: $rule->getUpdatedAt()?->format('c'),
        );
    }
}
