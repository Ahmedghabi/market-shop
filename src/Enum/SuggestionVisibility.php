<?php

namespace App\Enum;

enum SuggestionVisibility: string
{
    case PrivateVisibility = 'private';
    case ADMINS = 'admins';
    case PUBLIC = 'public';
}
