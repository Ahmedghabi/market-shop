<?php

namespace App\Enum;

enum MediaType: string
{
    case Image = 'IMAGE';
    case Video = 'VIDEO';
    case Document = 'DOCUMENT';
    case Audio = 'AUDIO';
}
