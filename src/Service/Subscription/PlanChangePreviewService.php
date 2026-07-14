<?php

namespace App\Service\Subscription;

use App\Dto\Subscription\PlanChangePreviewOutput;
use App\Entity\Boutique;
use App\Entity\SubscriptionPlan;
use App\Enum\ExtensionType;
use App\Enum\SubscriptionStatus;
use App\Repository\BoutiqueExtensionRepository;
use App\Repository\PlanQuotaRepository;
use App\Repository\QuotaDefinitionRepository;
use App\Repository\SubscriptionModuleRepository;

final readonly class PlanChangePreviewService
{
    public function __construct(
        private SubscriptionManager $subscriptionManager,
        private QuotaDefinitionRepository $quotaDefinitions,
        private PlanQuotaRepository $planQuotas,
        private BoutiqueExtensionRepository $boutiqueExtensions,
        private SubscriptionModuleRepository $subscriptionModules,
    ) {
    }

    public function preview(Boutique $boutique, SubscriptionPlan $newPlan): PlanChangePreviewOutput
    {
        $currentPlan = $this->subscriptionManager->getCurrentPlan($boutique);
        $output = new PlanChangePreviewOutput();
        $output->newPlanId = (string) $newPlan->getId();
        $output->newPlanName = $newPlan->getName();
        $output->newPlanPriceTnd = $newPlan->getPriceTnd();
        $output->currency = $newPlan->getCurrency();
        $output->durationMonths = $newPlan->getDurationMonths();
        $output->isRenewal = null !== $currentPlan && (string) $currentPlan->getId() === (string) $newPlan->getId();

        if (null !== $currentPlan) {
            $output->currentPlanId = (string) $currentPlan->getId();
            $output->currentPlanName = $currentPlan->getName();
        }

        $output->projectedEndDate = $this->projectEndDate($boutique, $newPlan)?->format('c');

        $newLimitMap = $this->planQuotas->findLimitMapByPlan($newPlan);
        $quotaChanges = [];
        foreach ($this->quotaDefinitions->findAllActive() as $quotaDef) {
            $code = $quotaDef->getCode();
            $currentLimit = $this->subscriptionManager->getLimit($code, $boutique);
            $usage = $this->subscriptionManager->getUsage($code, $boutique);
            $newBase = $newLimitMap[$code] ?? 0;
            $boost = $this->extensionBoost($boutique, $code);
            $newLimit = \array_key_exists($code, $newLimitMap) && null === $newLimitMap[$code]
                ? null
                : $newBase + $boost;

            $quotaChanges[] = [
                'code' => $code,
                'name' => $quotaDef->getName(),
                'currentLimit' => $currentLimit,
                'newLimit' => $newLimit,
                'currentUsage' => $usage,
                'diff' => null === $newLimit || null === $currentLimit
                    ? null
                    : $newLimit - ($currentLimit ?? 0),
            ];
        }
        $output->quotaChanges = $quotaChanges;

        $currentModules = $this->resolvePlanModuleCodes($currentPlan);
        $newModules = $this->resolvePlanModuleCodes($newPlan);
        $output->modulesGained = array_values(array_diff($newModules, $currentModules));
        $output->modulesLost = array_values(array_diff($currentModules, $newModules));

        $currentThemes = $this->themeCodesForPlan($currentPlan);
        $newThemes = $this->themeCodesForPlan($newPlan);
        $output->themesGained = $this->themeDiff($newPlan, array_diff($newThemes, $currentThemes));
        $output->themesLost = $this->themeDiff($currentPlan, array_diff($currentThemes, $newThemes));

        $output->extensionCompatibility = $this->buildExtensionCompatibility($boutique, $newPlan, $newModules, $newThemes);

        return $output;
    }

    private function projectEndDate(Boutique $boutique, SubscriptionPlan $newPlan): ?\DateTimeImmutable
    {
        if ($newPlan->getDurationMonths() <= 0) {
            return null;
        }

        $subscription = $boutique->getCurrentSubscription();
        $now = new \DateTimeImmutable();

        if ($subscription && SubscriptionStatus::Active === $subscription->getStatus()) {
            $currentEnd = $subscription->getEndDate();
            $baseDate = $currentEnd && $currentEnd > $now ? $currentEnd : $now;
        } else {
            $baseDate = $now;
        }

        return $baseDate->modify(sprintf('+%d months', $newPlan->getDurationMonths()));
    }

    private function basePlanLimit(?SubscriptionPlan $plan, string $quotaCode): int
    {
        if (null === $plan) {
            return 0;
        }
        $map = $this->planQuotas->findLimitMapByPlan($plan);

        return $map[$quotaCode] ?? 0;
    }

    private function extensionBoost(Boutique $boutique, string $quotaCode): int
    {
        $boost = 0;
        $now = new \DateTimeImmutable();
        foreach ($this->boutiqueExtensions->findActiveByBoutique($boutique) as $grant) {
            $extension = $grant->getExtension();
            if (ExtensionType::QuotaBoost !== $extension->getType() || $extension->getTargetCode() !== $quotaCode) {
                continue;
            }
            if ($grant->isExpired($now)) {
                continue;
            }
            $boost += $extension->getValue() ?? 0;
        }

        return $boost;
    }

    /** @return list<string> */
    private function resolvePlanModuleCodes(?SubscriptionPlan $plan): array
    {
        if (null === $plan) {
            return [];
        }

        $codes = array_values(array_filter((array) ($plan->getModules() ?? [])));
        foreach ($this->subscriptionModules->findAllowedModuleCodes($plan) as $code) {
            $codes[] = $code;
        }

        return array_values(array_unique($codes));
    }

    /** @return list<string> */
    private function themeCodesForPlan(?SubscriptionPlan $plan): array
    {
        if (null === $plan) {
            return [];
        }

        return array_map(
            static fn ($theme) => $theme->getCode(),
            $plan->getThemes()->toArray(),
        );
    }

    /**
     * @param list<string> $codes
     *
     * @return list<array{code: string, name: string}>
     */
    private function themeDiff(?SubscriptionPlan $plan, array $codes): array
    {
        if (null === $plan) {
            return [];
        }

        $result = [];
        foreach ($plan->getThemes() as $theme) {
            if (\in_array($theme->getCode(), $codes, true)) {
                $result[] = ['code' => $theme->getCode(), 'name' => $theme->getName()];
            }
        }

        return $result;
    }

    /**
     * @param list<string> $newModules
     * @param list<string> $newThemes
     *
     * @return list<array{code: string, name: string, compatible: bool}>
     */
    private function buildExtensionCompatibility(Boutique $boutique, SubscriptionPlan $newPlan, array $newModules, array $newThemes): array
    {
        $result = [];
        foreach ($this->boutiqueExtensions->findByBoutique($boutique) as $grant) {
            if (!$grant->isActive()) {
                continue;
            }
            $extension = $grant->getExtension();
            $compatible = match ($extension->getType()) {
                ExtensionType::QuotaBoost, ExtensionType::Service => true,
                ExtensionType::Module => \in_array($extension->getTargetCode(), $newModules, true),
                ExtensionType::Theme => \in_array($extension->getTargetCode(), $newThemes, true),
            };
            $result[] = [
                'code' => $extension->getCode(),
                'name' => $extension->getName(),
                'compatible' => $compatible,
            ];
        }

        return $result;
    }
}
