<?php

namespace App\State\Subscription;

use App\Entity\Boutique;
use App\Entity\SubscriptionPlan;
use App\Enum\ExtensionType;
use App\Repository\BoutiqueExtensionRepository;
use App\Repository\SubscriptionModuleRepository;

final readonly class SubscriptionExtensionReconciler
{
    public function __construct(
        private BoutiqueExtensionRepository $boutiqueExtensions,
        private SubscriptionModuleRepository $subscriptionModules,
    ) {
    }

    public function reconcileAfterPlanChange(Boutique $boutique, SubscriptionPlan $newPlan): int
    {
        $newModules = $this->moduleCodes($newPlan);
        $newThemes = array_map(
            static fn ($theme) => $theme->getCode(),
            $newPlan->getThemes()->toArray(),
        );

        $deactivated = 0;
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

            if (!$compatible) {
                $grant->deactivate();
                ++$deactivated;
            }
        }

        return $deactivated;
    }

    public function reactivateCompatibleGrants(Boutique $boutique, SubscriptionPlan $plan): int
    {
        $newModules = $this->moduleCodes($plan);
        $newThemes = array_map(
            static fn ($theme) => $theme->getCode(),
            $plan->getThemes()->toArray(),
        );
        $now = new \DateTimeImmutable();
        $reactivated = 0;

        foreach ($this->boutiqueExtensions->findByBoutique($boutique) as $grant) {
            if ($grant->isActive() || $grant->isExpired($now)) {
                continue;
            }

            $extension = $grant->getExtension();
            $compatible = match ($extension->getType()) {
                ExtensionType::QuotaBoost, ExtensionType::Service => true,
                ExtensionType::Module => \in_array($extension->getTargetCode(), $newModules, true),
                ExtensionType::Theme => \in_array($extension->getTargetCode(), $newThemes, true),
            };

            if ($compatible) {
                $grant->reactivate($grant->getExpiresAt());
                ++$reactivated;
            }
        }

        return $reactivated;
    }

    public function deactivateAllActiveGrants(Boutique $boutique): int
    {
        $count = 0;
        foreach ($this->boutiqueExtensions->findActiveByBoutique($boutique) as $grant) {
            $grant->deactivate();
            ++$count;
        }

        return $count;
    }

    /** @return list<string> */
    private function moduleCodes(SubscriptionPlan $plan): array
    {
        $codes = array_values(array_filter((array) ($plan->getModules() ?? [])));
        foreach ($this->subscriptionModules->findAllowedModuleCodes($plan) as $code) {
            $codes[] = $code;
        }

        return array_values(array_unique($codes));
    }
}
