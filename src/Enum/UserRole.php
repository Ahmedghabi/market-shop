<?php

namespace App\Enum;

enum UserRole: string
{
    case SuperAdmin = 'ROLE_SUPER_ADMIN';
    case BoutiqueAdmin = 'ROLE_BOUTIQUE_ADMIN';
    case Caissier = 'ROLE_CAISSIER';
    case Customer = 'ROLE_CUSTOMER';
}
