<?php

namespace App\Entity;

use App\Repository\ShopSocialProviderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ShopSocialProviderRepository::class)]
#[ORM\Table(name: 'shop_social_provider')]
#[ORM\UniqueConstraint(name: 'uniq_shop_social_provider', columns: ['boutique_id', 'social_provider_id'])]
class ShopSocialProvider extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private Boutique $boutique,
        #[ORM\ManyToOne(targetEntity: SocialProvider::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private SocialProvider $socialProvider,
        #[ORM\Column]
        private bool $isActive = false,
    ) {
        parent::__construct();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getSocialProvider(): SocialProvider
    {
        return $this->socialProvider;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }
}
