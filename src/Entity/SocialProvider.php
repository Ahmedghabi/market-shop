<?php

namespace App\Entity;

use App\Repository\SocialProviderRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SocialProviderRepository::class)]
#[ORM\Table(name: 'social_provider')]
class SocialProvider extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 32, unique: true)]
        private string $code,
        #[ORM\Column(length: 100)]
        private string $name,
        #[ORM\Column]
        private bool $isActive = false,
    ) {
        parent::__construct();
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getName(): string
    {
        return $this->name;
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
