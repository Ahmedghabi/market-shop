<?php

namespace App\Entity;

use App\Repository\SubscriptionPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SubscriptionPlanRepository::class)]
#[ORM\Table(name: 'subscription_plan')]
class SubscriptionPlan extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column]
        private int $durationMonths,
        #[ORM\Column]
        private int $priceTnd = 0,
        #[ORM\Column]
        private bool $isFree = false,
        #[ORM\Column]
        private bool $isVisible = true,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column(type: 'json', nullable: true)]
        private ?array $modules = null,
        #[ORM\Column(length: 64, nullable: true)]
        private ?string $chatbotModel = null,

        /** @var Collection<int, SubscriptionModule> */
        #[ORM\OneToMany(mappedBy: 'plan', targetEntity: SubscriptionModule::class, cascade: ['persist', 'remove'], orphanRemoval: true)]
        private Collection $subscriptionModules,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        $this->subscriptionModules = new ArrayCollection();
        parent::__construct();
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
        $this->touch();
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): void
    {
        $this->description = $description;
        $this->touch();
    }

    public function getDurationMonths(): int
    {
        return $this->durationMonths;
    }

    public function setDurationMonths(int $durationMonths): void
    {
        $this->durationMonths = $durationMonths;
        $this->touch();
    }

    public function getPriceTnd(): int
    {
        return $this->priceTnd;
    }

    public function setPriceTnd(int $priceTnd): void
    {
        $this->priceTnd = $priceTnd;
        $this->touch();
    }

    public function isFree(): bool
    {
        return $this->isFree;
    }

    public function setIsFree(bool $isFree): void
    {
        $this->isFree = $isFree;
        $this->touch();
    }

    public function isVisible(): bool
    {
        return $this->isVisible;
    }

    public function setIsVisible(bool $isVisible): void
    {
        $this->isVisible = $isVisible;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->touch();
    }

    public function getModules(): ?array
    {
        return $this->modules;
    }

    public function setModules(?array $modules): void
    {
        $this->modules = $modules;
        $this->touch();
    }

    public function getChatbotModel(): ?string
    {
        return $this->chatbotModel;
    }

    public function setChatbotModel(?string $chatbotModel): void
    {
        $this->chatbotModel = $chatbotModel;
        $this->touch();
    }

    /** @return Collection<int, SubscriptionModule> */
    public function getSubscriptionModules(): Collection
    {
        return $this->subscriptionModules;
    }

    public function addSubscriptionModule(SubscriptionModule $subscriptionModule): void
    {
        if (!$this->subscriptionModules->contains($subscriptionModule)) {
            $this->subscriptionModules->add($subscriptionModule);
        }
    }

    public function removeSubscriptionModule(SubscriptionModule $subscriptionModule): void
    {
        $this->subscriptionModules->removeElement($subscriptionModule);
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
