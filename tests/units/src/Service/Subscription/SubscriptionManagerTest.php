<?php

namespace App\Tests\Service\Subscription;

use App\Entity\Boutique;
use App\Entity\Subscription;
use App\Entity\SubscriptionPlan;
use App\Enum\PlanType;
use App\Enum\SubscriptionStatus;
use App\Service\Subscription\SubscriptionManager;
use PHPUnit\Framework\TestCase;

final class SubscriptionManagerTest extends TestCase
{
    public function testSubscriptionStateAndPlanAreReadFromBoutique(): void
    {
        $boutique = new Boutique('QA Shop', 'qa-shop');
        $subscription = new Subscription($boutique, PlanType::Free, SubscriptionStatus::Active);
        /** @var SubscriptionPlan $plan */
        $plan = (new \ReflectionClass(SubscriptionPlan::class))->newInstanceWithoutConstructor();
        $subscription->setSubscriptionPlan($plan);
        $boutique->setCurrentSubscription($subscription);

        $manager = $this->manager();

        self::assertTrue($manager->isSubscriptionActive($boutique));
        self::assertSame($plan, $manager->getCurrentPlan($boutique));
        self::assertTrue($manager->canCreateProduct($boutique));
    }

    public function testInactiveSubscriptionIsReportedAsInactive(): void
    {
        $boutique = new Boutique('QA Shop', 'qa-shop');
        $boutique->setCurrentSubscription(new Subscription($boutique, PlanType::Free, SubscriptionStatus::Expired));

        self::assertFalse($this->manager()->isSubscriptionActive($boutique));
    }

    private function manager(): SubscriptionManager
    {
        /** @var SubscriptionManager $manager */
        $manager = (new \ReflectionClass(SubscriptionManager::class))->newInstanceWithoutConstructor();

        return $manager;
    }
}
