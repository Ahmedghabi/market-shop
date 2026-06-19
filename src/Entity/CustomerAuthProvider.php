<?php

namespace App\Entity;

use App\Repository\CustomerAuthProviderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerAuthProviderRepository::class)]
#[ORM\Table(name: 'customer_auth_provider')]
#[ORM\UniqueConstraint(name: 'uniq_customer_provider', columns: ['customer_id', 'provider'])]
class CustomerAuthProvider extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Customer::class, inversedBy: 'authProviders')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Customer $customer,
        #[ORM\Column(length: 32)]
        private string $provider,
        #[ORM\Column(length: 255)]
        private string $providerUserId,
    ) {
        parent::__construct();
    }

    public function getCustomer(): Customer
    {
        return $this->customer;
    }

    public function getProvider(): string
    {
        return $this->provider;
    }

    public function getProviderUserId(): string
    {
        return $this->providerUserId;
    }
}
