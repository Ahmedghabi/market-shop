<?php

namespace App\Enum;

enum PlanType: string
{
    case Free = 'free';
    case ThreeMonths = '3months';
    case SixMonths = '6months';
    case OneYear = '1year';

    public function label(): string
    {
        return match ($this) {
            self::Free => 'Gratuit',
            self::ThreeMonths => '3 mois',
            self::SixMonths => '6 mois',
            self::OneYear => '1 an',
        };
    }

    public function durationMonths(): int
    {
        return match ($this) {
            self::Free => 1,
            self::ThreeMonths => 3,
            self::SixMonths => 6,
            self::OneYear => 12,
        };
    }

    public function priceCents(): int
    {
        return match ($this) {
            self::Free => 0,
            self::ThreeMonths => 2999,
            self::SixMonths => 4999,
            self::OneYear => 8999,
        };
    }
}
