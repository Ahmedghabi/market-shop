<?php

namespace App\Enum;

enum OrderMode: string
{
    case Ecommerce = 'ECOMMERCE';
    case OrderOnly = 'ORDER_ONLY';
    case WhatsApp = 'WHATSAPP';
    case QuoteRequest = 'QUOTE_REQUEST';
    case Catalog = 'CATALOG';
}
