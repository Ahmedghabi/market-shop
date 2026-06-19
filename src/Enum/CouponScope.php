<?php

namespace App\Enum;

enum CouponScope: string
{
    case Global = 'GLOBAL';
    case Product = 'PRODUCT';
    case Category = 'CATEGORY';
    case Brand = 'BRAND';
    case Cart = 'CART';
}
