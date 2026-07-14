<?php

namespace App\Service\Subscription;

use App\Entity\Boutique;
use App\Entity\Brand;
use App\Entity\Category;
use App\Entity\Customer;
use App\Entity\Product;
use App\Entity\SubscriptionPlan;
use App\Entity\Theme;
use App\Entity\UserShop;
use App\Enum\ExtensionType;
use App\Repository\BoutiqueExtensionRepository;
use App\Repository\PlanQuotaRepository;
use App\Service\Module\ModuleAccessService;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Single centralized gate for everything related to a boutique's subscription rights:
 * quotas, modules, themes and subscription state. No other service/module should compute
 * these checks directly - they must all go through here so the rules stay in one place.
 *
 * Formula (per the specification):
 *   Capacite finale      = Quotas du plan + Extensions actives (quota boosts)
 *   Modules disponibles  = Modules du plan + Extensions actives (module extensions)
 *   Themes disponibles   = Themes du plan + Extensions actives (theme extensions)
 */
final readonly class SubscriptionManager
{
    /**
     * Maps a quota code to the entity class used to count current usage for a boutique.
     * Adding a new countable quota only requires adding one line here - no other change needed.
     *
     * @var array<string, class-string>
     */
    private const QUOTA_ENTITY_MAP = [
        'max_products' => Product::class,
        'max_categories' => Category::class,
        'max_customers' => Customer::class,
        'max_brands' => Brand::class,
    ];

    public function __construct(
        private EntityManagerInterface $em,
        private PlanQuotaRepository $planQuotas,
        private BoutiqueExtensionRepository $boutiqueExtensions,
        private ModuleAccessService $moduleAccess,
    ) {
    }

    public function isSubscriptionActive(Boutique $boutique): bool
    {
        return $boutique->hasActiveSubscription();
    }

    public function getCurrentPlan(Boutique $boutique): ?SubscriptionPlan
    {
        $subscription = $boutique->getCurrentSubscription();

        return $subscription?->getSubscriptionPlan();
    }

    public function hasModule(string $moduleCode, Boutique $boutique): bool
    {
        if ($this->moduleAccess->isModuleEnabled($moduleCode, $boutique)) {
            return true;
        }

        // Extensions can unlock a module that isn't included in the plan, but platform
        // and shop-level toggles still apply - only the "plan" layer is bypassed.
        if (!$this->hasActiveExtensionGrant($boutique, ExtensionType::Module, $moduleCode)) {
            return false;
        }

        $module = $this->em->getRepository(\App\Entity\SubscriptionPlanModule::class)->findOneBy(['code' => $moduleCode]);
        if (null === $module) {
            return false;
        }

        return $this->moduleAccess->isGloballyEnabled($module, $boutique)
            && $this->moduleAccess->isEnabledInBoutique($module, $boutique);
    }

    public function hasTheme(string $themeCode, Boutique $boutique): bool
    {
        foreach ($this->getAccessibleThemes($boutique) as $theme) {
            if ($theme->getCode() === $themeCode) {
                return true;
            }
        }

        return false;
    }

    /** @return Theme[] */
    public function getAccessibleThemes(Boutique $boutique): array
    {
        $plan = $this->getCurrentPlan($boutique);
        $themes = [];

        if (null !== $plan) {
            foreach ($plan->getThemes() as $theme) {
                $themes[(string) $theme->getId()] = $theme;
            }
        }

        foreach ($this->boutiqueExtensions->findActiveByBoutique($boutique) as $grant) {
            $extension = $grant->getExtension();
            if (ExtensionType::Theme !== $extension->getType() || $grant->isExpired(new \DateTimeImmutable())) {
                continue;
            }

            $theme = $this->em->getRepository(Theme::class)->findOneBy(['code' => $extension->getTargetCode()]);
            if (null !== $theme) {
                $themes[(string) $theme->getId()] = $theme;
            }
        }

        return array_values($themes);
    }

    /**
     * Null return means unlimited. Combines the plan's base limit with any active
     * quota-boost extensions for the same quota code.
     */
    public function getLimit(string $quotaCode, Boutique $boutique): ?int
    {
        $plan = $this->getCurrentPlan($boutique);
        $baseLimit = 0;
        $unlimited = null === $plan;

        if (null !== $plan) {
            $limitMap = $this->planQuotas->findLimitMapByPlan($plan);
            if (\array_key_exists($quotaCode, $limitMap)) {
                if (null === $limitMap[$quotaCode]) {
                    $unlimited = true;
                } else {
                    $baseLimit = $limitMap[$quotaCode];
                }
            }
        }

        $boost = 0;
        foreach ($this->boutiqueExtensions->findActiveByBoutique($boutique) as $grant) {
            $extension = $grant->getExtension();
            if (ExtensionType::QuotaBoost !== $extension->getType() || $extension->getTargetCode() !== $quotaCode) {
                continue;
            }
            if ($grant->isExpired(new \DateTimeImmutable())) {
                continue;
            }
            $boost += $extension->getValue() ?? 0;
        }

        if ($unlimited) {
            return null;
        }

        return $baseLimit + $boost;
    }

    public function getUsage(string $quotaCode, Boutique $boutique): int
    {
        if ('max_employees' === $quotaCode) {
            return $this->em->getRepository(UserShop::class)->count(['boutique' => $boutique, 'role' => 'ROLE_CAISSIER']);
        }

        if ('max_admins' === $quotaCode) {
            return $this->em->getRepository(UserShop::class)->count(['boutique' => $boutique, 'role' => 'ROLE_BOUTIQUE_ADMIN']);
        }

        $entityClass = self::QUOTA_ENTITY_MAP[$quotaCode] ?? null;
        if (null === $entityClass) {
            // Not-yet-trackable quota (future extensibility): treated as unrestricted usage.
            return 0;
        }

        return $this->em->getRepository($entityClass)->count(['boutique' => $boutique]);
    }

    /** Null return means unlimited remaining. */
    public function getRemainingQuota(string $quotaCode, Boutique $boutique): ?int
    {
        $limit = $this->getLimit($quotaCode, $boutique);
        if (null === $limit) {
            return null;
        }

        return max(0, $limit - $this->getUsage($quotaCode, $boutique));
    }

    public function canCreateProduct(Boutique $boutique): bool
    {
        return $this->canCreate('max_products', $boutique);
    }

    public function canCreateCategory(Boutique $boutique): bool
    {
        return $this->canCreate('max_categories', $boutique);
    }

    public function canCreateEmployee(Boutique $boutique): bool
    {
        return $this->canCreate('max_employees', $boutique);
    }

    public function canCreateCustomer(Boutique $boutique): bool
    {
        return $this->canCreate('max_customers', $boutique);
    }

    public function canCreateBrand(Boutique $boutique): bool
    {
        return $this->canCreate('max_brands', $boutique);
    }

    private function canCreate(string $quotaCode, Boutique $boutique): bool
    {
        if (!$this->isSubscriptionActive($boutique)) {
            return false;
        }

        $remaining = $this->getRemainingQuota($quotaCode, $boutique);

        return null === $remaining || $remaining > 0;
    }

    private function hasActiveExtensionGrant(Boutique $boutique, ExtensionType $type, string $targetCode): bool
    {
        $now = new \DateTimeImmutable();
        foreach ($this->boutiqueExtensions->findActiveByBoutique($boutique) as $grant) {
            $extension = $grant->getExtension();
            if ($extension->getType() === $type && $extension->getTargetCode() === $targetCode && !$grant->isExpired($now)) {
                return true;
            }
        }

        return false;
    }
}
