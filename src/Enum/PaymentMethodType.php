<?php

namespace App\Enum;

enum PaymentMethodType: string
{
    case CashOnDelivery = 'CASH_ON_DELIVERY';
    case BankTransfer = 'BANK_TRANSFER';
    case CardPayment = 'CARD_PAYMENT';
    case MobilePayment = 'MOBILE_PAYMENT';
    case Wallet = 'WALLET';
    case ExternalGateway = 'EXTERNAL_GATEWAY';
}
