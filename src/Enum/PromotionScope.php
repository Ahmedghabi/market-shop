<?php

namespace App\Enum;

enum PromotionScope: string
{
    case Global = 'global';
    case Category = 'category';
    case Product = 'product';
}
