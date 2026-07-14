<?php

namespace App\State\Loyalty;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Loyalty\LoyaltyRuleInput;
use App\Dto\Loyalty\LoyaltyRuleOutput;
use App\Entity\LoyaltyReward;
use App\Entity\LoyaltyRule;
use App\Repository\BoutiqueRepository;
use App\Repository\LoyaltyRewardRepository;
use App\Security\BoutiqueContext;
use App\Service\Loyalty\LoyaltyCacheService;
use App\Service\Loyalty\LoyaltyEngine;
use App\State\Common\BoutiqueWriteResolverTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<LoyaltyRuleOutput|null> */
final readonly class LoyaltyRuleProcessor implements ProcessorInterface
{
    use BoutiqueWriteResolverTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
        private LoyaltyRewardRepository $rewards,
        private EntityManagerInterface $em,
        private LoyaltyEngine $engine,
        private LoyaltyCacheService $cache,
        private LoyaltyRuleProvider $provider,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?LoyaltyRuleOutput
    {
        $boutique = $this->resolveBoutiqueForWrite($data, $uriVariables, $context);

        if ($operation instanceof Delete) {
            $rule = $this->provider->findRule($boutique, (string) ($uriVariables['id'] ?? ''));
            if (!$rule instanceof LoyaltyRule) {
                throw new NotFoundHttpException('Règle de fidélité introuvable');
            }
            $this->em->remove($rule);
            $this->em->flush();
            $this->cache->invalidateRules((string) $boutique->getId());

            return null;
        }

        assert($data instanceof LoyaltyRuleInput);
        $program = $this->engine->getOrCreateProgram($boutique);
        $unlockedReward = null !== $data->unlockedRewardId ? $this->rewards->find($data->unlockedRewardId) : null;

        $rule = isset($uriVariables['id']) ? $this->provider->findRule($boutique, (string) $uriVariables['id']) : null;
        if (isset($uriVariables['id']) && !$rule instanceof LoyaltyRule) {
            throw new NotFoundHttpException('Règle de fidélité introuvable');
        }

        if (!$rule instanceof LoyaltyRule) {
            $rule = new LoyaltyRule(program: $program, name: $data->name, triggerCode: $data->triggerCode);
            $this->em->persist($rule);
        }

        $this->applyFields($rule, $data, $unlockedReward instanceof LoyaltyReward ? $unlockedReward : null);

        $this->em->flush();
        $this->cache->invalidateRules((string) $boutique->getId());

        return $this->provider->toOutput($rule);
    }

    private function applyFields(LoyaltyRule $rule, LoyaltyRuleInput $d, ?LoyaltyReward $unlockedReward): void
    {
        $rule->setName($d->name);
        $rule->setDescription($d->description);
        $rule->setTriggerCode($d->triggerCode);
        $rule->setTriggerConfig($d->triggerConfig);
        $rule->setRewardPoints($d->rewardPoints);
        $rule->setIsMultiplier($d->isMultiplier);
        $rule->setMultiplierValue($d->multiplierValue);
        $rule->setAppliesToTriggerCodes($d->appliesToTriggerCodes);
        $rule->setUnlockedReward($unlockedReward);
        $rule->setPriority($d->priority);
        $rule->setActive($d->isActive);
        $rule->setCumulative($d->isCumulative);
        $rule->setStartsAt(null !== $d->startsAt ? new \DateTimeImmutable($d->startsAt) : null);
        $rule->setEndsAt(null !== $d->endsAt ? new \DateTimeImmutable($d->endsAt) : null);
        $rule->setActiveDaysOfWeek($d->activeDaysOfWeek);
        $rule->setMaxTriggersPerCustomer($d->maxTriggersPerCustomer);
        $rule->setMaxTriggersPerPeriod($d->maxTriggersPerPeriod);
        $rule->setPeriodType($d->periodType);
    }
}
