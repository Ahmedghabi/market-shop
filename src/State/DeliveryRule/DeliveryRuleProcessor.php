<?php

namespace App\State\DeliveryRule;

use App\Dto\DeliveryRule\DeliveryRuleInput;
use App\Service\Delivery\DeliveryRuleService;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;

final class DeliveryRuleProcessor implements ProcessorInterface
{
    public function __construct(
        private DeliveryRuleService $ruleService,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        $boutiqueId = $uriVariables['boutiqueId'] ?? '';

        if ($data instanceof DeliveryRuleInput) {
            if (isset($uriVariables['id'])) {
                $rule = $this->ruleService->getRuleById($uriVariables['id']);
                if ($rule) {
                    return $this->ruleService->update($rule, (array) $data);
                }
            }

            return $this->ruleService->create($boutiqueId, (array) $data);
        }

        return null;
    }
}
