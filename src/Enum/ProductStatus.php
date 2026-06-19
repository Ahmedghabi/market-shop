<?php

namespace App\Enum;

enum ProductStatus: string
{
    case Draft = 'DRAFT';
    case Active = 'ACTIVE';
    case Inactive = 'INACTIVE';
    case Archived = 'ARCHIVED';
}
