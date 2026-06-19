<?php

namespace App\Enum\Chat;

enum DisplayType: string
{
    case Banner = 'banner';
    case Popup = 'popup';
    case Floating = 'floating';
    case Inline = 'inline';
}
