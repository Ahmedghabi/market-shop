<?php

namespace App\Enum\Chat;

enum SenderType: string
{
    case User = 'user';
    case Admin = 'admin';
    case SuperAdmin = 'super_admin';
    case Bot = 'bot';
}
