<?php

namespace App\Dto\Cart;

final class CartOutput
{
    public string $id;
    public string $boutiqueId;
    public string $status;
    public string $currency;
    public int $itemsCount;
    public int $totalCents;
    public ?string $customerEmail = null;
    public ?string $firstName = null;
    public ?string $lastName = null;
    public ?string $phone = null;
    public ?string $address = null;
    public ?string $city = null;
    public ?string $postalCode = null;
    public ?string $country = null;
    public ?string $countryId = null;
    public ?string $governorate = null;
    public ?string $governorateId = null;
    public ?string $locality = null;
    public ?string $localityId = null;
    /** @var list<CartItemOutput> */
    public array $items = [];
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;
    public \DateTimeImmutable $expiresAt;
}
