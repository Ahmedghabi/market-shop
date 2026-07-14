<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Loyalty\LoyaltyRuleOutput;
use App\Entity\LoyaltyRule;
use App\Repository\BoutiqueRepository;
use App\Repository\LoyaltyRuleRepository;
use App\Security\BoutiqueContext;
use App\Service\Loyalty\LoyaltyEngine;
use App\State\Common\BoutiqueAwareProviderTrait;

/** @implements ProviderInterface<LoyaltyRuleOutput> */
final readonly class LoyaltyRuleProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private LoyaltyRuleRepository $rules,
        private BoutiqueContext $context,
        private LoyaltyEngine $engine,
    ) {
    }

    /** @return list<LoyaltyRuleOutput>|LoyaltyRuleOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|LoyaltyRuleOutput|null
    {
        // Not just Get: Patch/Delete/Put also read a single item first (see ReadProvider).
        $isItemOperation = !$operation instanceof GetCollection && isset($uriVariables['id']);

        $boutique = $this->resolveBoutiqueFromRequest($context, $uriVariables);
        if (null === $boutique || !$this->context->canAccessBoutique($boutique)) {
            return $isItemOperation ? null : [];
        }

        if ($isItemOperation) {
            $rule = $this->findRule($boutique, (string) ($uriVariables['id'] ?? ''));

            return $rule instanceof LoyaltyRule ? $this->toOutput($rule) : null;
        }

        $program = $this->engine->getOrCreateProgram($boutique);

        return array_map($this->toOutput(...), $this->rules->findByProgram($program));
    }

    public function findRule(\App\Entity\Boutique $boutique, string $id): ?LoyaltyRule
    {
        $rule = $this->rules->find($id);

        return $rule instanceof LoyaltyRule && (string) $rule->getProgram()->getBoutique()->getId() === (string) $boutique->getId() ? $rule : null;
    }

    public function toOutput(LoyaltyRule $rule): LoyaltyRuleOutput
    {
        $output = new LoyaltyRuleOutput();
        $output->id = (string) $rule->getId();
        $output->programId = (string) $rule->getProgram()->getId();
        $output->name = $rule->getName();
        $output->description = $rule->getDescription();
        $output->triggerCode = $rule->getTriggerCode();
        $output->triggerConfig = $rule->getTriggerConfig();
        $output->rewardPoints = $rule->getRewardPoints();
        $output->isMultiplier = $rule->isMultiplier();
        $output->multiplierValue = $rule->getMultiplierValue();
        $output->appliesToTriggerCodes = $rule->getAppliesToTriggerCodes();
        $output->unlockedRewardId = $rule->getUnlockedReward()?->getId()->toRfc4122();
        $output->priority = $rule->getPriority();
        $output->isActive = $rule->isActive();
        $output->isCumulative = $rule->isCumulative();
        $output->startsAt = $rule->getStartsAt()?->format('c');
        $output->endsAt = $rule->getEndsAt()?->format('c');
        $output->activeDaysOfWeek = $rule->getActiveDaysOfWeek();
        $output->maxTriggersPerCustomer = $rule->getMaxTriggersPerCustomer();
        $output->maxTriggersPerPeriod = $rule->getMaxTriggersPerPeriod();
        $output->periodType = $rule->getPeriodType();
        $output->createdAt = $rule->getCreatedAt();
        $output->updatedAt = $rule->getUpdatedAt();

        return $output;
    }
}
