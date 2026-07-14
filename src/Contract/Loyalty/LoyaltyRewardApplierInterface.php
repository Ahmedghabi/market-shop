<?php

namespace App\Contract\Loyalty;

use App\Entity\LoyaltyReward;
use App\Service\Loyalty\Dto\LoyaltyEvaluationContext;
use App\Service\Loyalty\Dto\LoyaltyRewardApplicationResult;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/**
 * Implement this interface to add a new redeemable reward type. Any class
 * implementing it is automatically registered with LoyaltyRewardApplierRegistry —
 * no edits to LoyaltyEngine, entities, or other appliers are required.
 */
#[AutoconfigureTag('app.loyalty.reward_applier')]
interface LoyaltyRewardApplierInterface
{
    /**
     * Unique code stored in LoyaltyReward::$typeCode (e.g. "fixed_discount").
     */
    public function getCode(): string;

    /**
     * Human-readable label for the admin reward-builder UI.
     */
    public function getLabel(): string;

    /**
     * Computes the monetary/flag effect of the reward. Compute-only: no
     * OrderItem creation or delivery-fee mutation happens here.
     */
    public function apply(LoyaltyReward $reward, LoyaltyEvaluationContext $context): LoyaltyRewardApplicationResult;
}
