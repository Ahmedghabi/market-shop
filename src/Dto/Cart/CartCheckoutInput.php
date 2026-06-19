<?php

namespace App\Dto\Cart;

use Symfony\Component\Validator\Constraints as Assert;

final class CartCheckoutInput
{
    public ?string $paymentMethodCode = null;

    #[Assert\NotBlank]
    #[Assert\Email]
    public ?string $customerEmail = null;

    #[Assert\NotBlank]
    public ?string $firstName = null;

    public ?string $lastName = null;

    #[Assert\NotBlank]
    public ?string $phone = null;

    #[Assert\NotBlank]
    public ?string $shippingAddress = null;

    public ?string $shippingCity = null;

    public ?string $shippingPostalCode = null;

    public ?string $shippingCountry = null;

    public ?string $shippingCountryId = null;

    public ?string $shippingGovernorate = null;

    public ?string $shippingGovernorateId = null;

    public ?string $shippingLocality = null;

    public ?string $shippingLocalityId = null;
}
