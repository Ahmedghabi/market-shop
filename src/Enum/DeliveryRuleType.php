<?php

namespace App\Enum;

enum DeliveryRuleType: string
{
    case FreeDelivery = 'FREE_DELIVERY';
    case FixedPrice = 'FIXED_PRICE';
    case PriceByWeight = 'PRICE_BY_WEIGHT';
    case PriceByDistance = 'PRICE_BY_DISTANCE';
    case PriceByCartAmount = 'PRICE_BY_CART_AMOUNT';
    case ExpressDelivery = 'EXPRESS_DELIVERY';
}
