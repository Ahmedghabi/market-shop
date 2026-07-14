<?php

namespace App\Enum;

enum SuggestionReactionType: string
{
    case LIKE = 'like';
    case SUPPORT = 'support';
    case PRIORITY = 'priority';
    case INTERESTING = 'interesting';
    case NOT_USEFUL = 'not_useful';
}
