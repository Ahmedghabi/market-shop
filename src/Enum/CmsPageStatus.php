<?php

namespace App\Enum;

enum CmsPageStatus: string
{
    case Draft = 'DRAFT';
    case Published = 'PUBLISHED';
    case Archived = 'ARCHIVED';
}
