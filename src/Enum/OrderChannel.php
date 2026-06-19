<?php

namespace App\Enum;

enum OrderChannel: string
{
    case Online = 'online';
    case Pos = 'pos';
}
