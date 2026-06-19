<?php

namespace App\Entity;

use App\Doctrine\Traits\SoftDeleteTrait;
use App\Entity\Contract\SoftDeletableInterface;
use App\Repository\CustomerRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomerRepository::class)]
#[ORM\Table(name: 'customer')]
#[ORM\UniqueConstraint(name: 'uniq_customer_boutique_email', columns: ['boutique_id', 'email'])]
class Customer extends AbstractEntity implements SoftDeletableInterface
{
    use SoftDeleteTrait;

    /** @var Collection<int, CustomerAuthProvider> */
    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: CustomerAuthProvider::class, cascade: ['persist'], orphanRemoval: true)]
    private ?Collection $authProviders = null;

    /** @var Collection<int, CustomerLoyalty> */
    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: CustomerLoyalty::class, cascade: ['persist'], orphanRemoval: true)]
    private ?Collection $loyaltyRecords = null;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class, inversedBy: 'customers')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 180)]
        private string $email,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $firstName = null,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $lastName = null,
        #[ORM\Column(length: 64, nullable: true)]
        private ?string $phone = null,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $address = null,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $city = null,
        #[ORM\Column(length: 32, nullable: true)]
        private ?string $postalCode = null,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $country = null,
        #[ORM\Column(type: 'uuid', nullable: true)]
        private ?string $countryId = null,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $governorate = null,
        #[ORM\Column(type: 'uuid', nullable: true)]
        private ?string $governorateId = null,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $locality = null,
        #[ORM\Column(type: 'uuid', nullable: true)]
        private ?string $localityId = null,
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
        private ?User $user = null,
        #[ORM\Column]
        private int $loyaltyPoints = 0,
    ) {
        parent::__construct();
        $this->authProviders = new ArrayCollection();
        $this->loyaltyRecords = new ArrayCollection();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(?string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(?string $lastName): void
    {
        $this->lastName = $lastName;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): void
    {
        $this->phone = $phone;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getPostalCode(): ?string
    {
        return $this->postalCode;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getGovernorate(): ?string
    {
        return $this->governorate;
    }

    public function getCountryId(): ?string
    {
        return $this->countryId;
    }

    public function getGovernorateId(): ?string
    {
        return $this->governorateId;
    }

    public function getLocality(): ?string
    {
        return $this->locality;
    }

    public function getLocalityId(): ?string
    {
        return $this->localityId;
    }

    public function setAddressSnapshot(
        ?string $address,
        ?string $city,
        ?string $postalCode,
        ?string $country,
        ?string $countryId,
        ?string $governorate,
        ?string $governorateId,
        ?string $locality,
        ?string $localityId,
    ): void {
        $this->address = $address;
        $this->city = $city;
        $this->postalCode = $postalCode;
        $this->country = $country;
        $this->countryId = $countryId;
        $this->governorate = $governorate;
        $this->governorateId = $governorateId;
        $this->locality = $locality;
        $this->localityId = $localityId;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    public function getLoyaltyPoints(): int
    {
        return $this->loyaltyPoints;
    }

    public function setLoyaltyPoints(int $loyaltyPoints): void
    {
        $this->loyaltyPoints = $loyaltyPoints;
    }

    /** @return Collection<int, CustomerAuthProvider> */
    public function getAuthProviders(): Collection
    {
        return $this->authProviders ?? new ArrayCollection();
    }

    /** @return Collection<int, CustomerLoyalty> */
    public function getLoyaltyRecords(): Collection
    {
        return $this->loyaltyRecords ?? new ArrayCollection();
    }
}
