<?php

namespace App\Enum;

enum SuggestionStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case ANALYZING = 'analyzing';
    case ACCEPTED = 'accepted';
    case PLANNED = 'planned';
    case IN_DEVELOPMENT = 'in_development';
    case IMPLEMENTED = 'implemented';
    case REJECTED = 'rejected';
    case ARCHIVED = 'archived';
}
