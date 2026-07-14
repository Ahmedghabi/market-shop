<?php

namespace App\Enum;

enum LoyaltyValidityPolicy: string
{
    case Never = 'never';
    case Days30 = 'days_30';
    case Days90 = 'days_90';
    case Days180 = 'days_180';
    case Days365 = 'days_365';
    case Custom = 'custom';

    /**
     * Number of days points remain valid for this policy, or null when points never expire
     * or the duration is defined by a separate "custom days" value.
     */
    public function days(): ?int
    {
        return match ($this) {
            self::Never, self::Custom => null,
            self::Days30 => 30,
            self::Days90 => 90,
            self::Days180 => 180,
            self::Days365 => 365,
        };
    }
}
