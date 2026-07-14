<?php

namespace App\Dto\Cart;

final class CartCheckoutOutput
{
    public string $orderId;
    public string $cartId;
    public string $status;
    public string $paymentStatus;
    public ?string $paymentMethodCode;
    public int $totalCents;
    public string $currency;
    public ?string $customerName = null;
    public int $subtotalCents = 0;
    public int $loyaltyDiscountCents = 0;
    public int $loyaltyPointsUsed = 0;
}
