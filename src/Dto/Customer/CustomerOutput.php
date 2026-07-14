<?php

namespace App\Dto\Customer;

final class CustomerOutput
{
    public string $id;
    public string $boutiqueId;
    public string $email;
    public string $firstName;
    public string $lastName;
    public ?string $phone;
    public ?string $address;
    public ?string $city;
    public ?string $postalCode;
    public ?string $country;
    public int $ordersCount = 0;
    public float $totalSpent = 0.0;
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;
}
