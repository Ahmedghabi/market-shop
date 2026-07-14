<?php

namespace App\State\DeliveryRule;

use App\Dto\DeliveryRule\DeliveryRuleOutput;
use App\Entity\DeliveryRule;
use App\Repository\DeliveryRuleRepository;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\State\Common\BoutiqueAwareProviderTrait;

final class DeliveryRuleProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private DeliveryRuleRepository $rules,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        if ($operation instanceof GetCollection) {
            $boutique = $this->resolveBoutiqueFromRequest($context);
            if (!$boutique) {
                return [];
            }

            $rules = $this->rules->findByBoutique($boutique);

            return array_map($this->toOutput(...), $rules);
        }

        $rule = $this->rules->find($uriVariables['id'] ?? null);
        if (!$rule instanceof DeliveryRule) {
            return null;
        }

        $boutique = $this->resolveBoutiqueFromRequest($context);
        if ($boutique && $rule->getBoutique()->getId() !== $boutique->getId()) {
            return null;
        }

        return $this->toOutput($rule);
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
