<?php

namespace App\Entity;

use App\Enum\UserStatus;
use App\Repository\UserShopRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserShopRepository::class)]
#[ORM\Table(name: 'user_shop')]
#[ORM\UniqueConstraint(name: 'uniq_user_shop', columns: ['user_id', 'boutique_id'])]
class UserShop extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'userShops')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private User $user,
        #[ORM\ManyToOne(targetEntity: Boutique::class, inversedBy: 'userShops')]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\Column(length: 32)]
        private string $role,
        #[ORM\Column(length: 32, enumType: UserStatus::class)]
        private UserStatus $status = UserStatus::Pending,
        #[ORM\Column(type: 'uuid', nullable: true)]
        private ?string $createdBy = null,
    ) {
        parent::__construct();
    }

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function setRole(string $role): void
    {
        $this->role = $role;
    }

    public function getStatus(): UserStatus
    {
        return $this->status;
    }

    public function setStatus(UserStatus $status): void
    {
        $this->status = $status;
    }

    public function getCreatedBy(): ?string
    {
        return $this->createdBy;
    }
}
