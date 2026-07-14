<?php

namespace App\Entity;

use App\Repository\LoyaltyRuleRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * A configurable earning rule. `triggerCode` is validated against the
 * LoyaltyTriggerRegistry rather than a closed PHP enum, so new triggers can be
 * added (new evaluator class) without ever touching this entity.
 */
#[ORM\Entity(repositoryClass: LoyaltyRuleRepository::class)]
#[ORM\Table(name: 'loyalty_rule')]
#[ORM\Index(name: 'idx_loyalty_rule_program', columns: ['program_id'])]
#[ORM\Index(name: 'idx_loyalty_rule_trigger', columns: ['trigger_code'])]
class LoyaltyRule extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: LoyaltyProgram::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private LoyaltyProgram $program,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column(length: 64)]
        private string $triggerCode = 'order_amount',
        #[ORM\Column(type: 'json')]
        private array $triggerConfig = [],
        #[ORM\Column]
        private int $rewardPoints = 0,
        #[ORM\Column]
        private bool $isMultiplier = false,
        #[ORM\Column]
        private float $multiplierValue = 1.0,
        #[ORM\Column(type: 'json', nullable: true)]
        private ?array $appliesToTriggerCodes = null,
        #[ORM\ManyToOne(targetEntity: LoyaltyReward::class)]
        #[ORM\JoinColumn(name: 'unlocked_reward_id', nullable: true, onDelete: 'SET NULL')]
        private ?LoyaltyReward $unlockedReward = null,
        #[ORM\Column]
        private int $priority = 0,
        #[ORM\Column]
        private bool $isActive = true,
        #[ORM\Column]
        private bool $isCumulative = true,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $startsAt = null,
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $endsAt = null,
        #[ORM\Column(type: 'json', nullable: true)]
        private ?array $activeDaysOfWeek = null,
        #[ORM\Column(nullable: true)]
        private ?int $maxTriggersPerCustomer = null,
        #[ORM\Column(nullable: true)]
        private ?int $maxTriggersPerPeriod = null,
        #[ORM\Column(length: 16, nullable: true)]
        private ?string $periodType = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getProgram(): LoyaltyProgram
    {
        return $this->program;
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

    public function getTriggerCode(): string
    {
        return $this->triggerCode;
    }

    public function setTriggerCode(string $triggerCode): void
    {
        $this->triggerCode = $triggerCode;
        $this->touch();
    }

    /** @return array<string, mixed> */
    public function getTriggerConfig(): array
    {
        return $this->triggerConfig;
    }

    /** @param array<string, mixed> $config */
    public function setTriggerConfig(array $config): void
    {
        $this->triggerConfig = $config;
        $this->touch();
    }

    public function getRewardPoints(): int
    {
        return $this->rewardPoints;
    }

    public function setRewardPoints(int $points): void
    {
        $this->rewardPoints = $points;
        $this->touch();
    }

    public function isMultiplier(): bool
    {
        return $this->isMultiplier;
    }

    public function setIsMultiplier(bool $isMultiplier): void
    {
        $this->isMultiplier = $isMultiplier;
        $this->touch();
    }

    public function getMultiplierValue(): float
    {
        return $this->multiplierValue;
    }

    public function setMultiplierValue(float $value): void
    {
        $this->multiplierValue = $value;
        $this->touch();
    }

    /** @return list<string>|null */
    public function getAppliesToTriggerCodes(): ?array
    {
        return $this->appliesToTriggerCodes;
    }

    /** @param list<string>|null $codes */
    public function setAppliesToTriggerCodes(?array $codes): void
    {
        $this->appliesToTriggerCodes = $codes;
        $this->touch();
    }

    public function getUnlockedReward(): ?LoyaltyReward
    {
        return $this->unlockedReward;
    }

    public function setUnlockedReward(?LoyaltyReward $reward): void
    {
        $this->unlockedReward = $reward;
        $this->touch();
    }

    public function getPriority(): int
    {
        return $this->priority;
    }

    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
        $this->touch();
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setActive(bool $isActive): void
    {
        $this->isActive = $isActive;
        $this->touch();
    }

    public function isCumulative(): bool
    {
        return $this->isCumulative;
    }

    public function setCumulative(bool $isCumulative): void
    {
        $this->isCumulative = $isCumulative;
        $this->touch();
    }

    public function getStartsAt(): ?\DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function setStartsAt(?\DateTimeImmutable $startsAt): void
    {
        $this->startsAt = $startsAt;
        $this->touch();
    }

    public function getEndsAt(): ?\DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function setEndsAt(?\DateTimeImmutable $endsAt): void
    {
        $this->endsAt = $endsAt;
        $this->touch();
    }

    /** @return list<int>|null */
    public function getActiveDaysOfWeek(): ?array
    {
        return $this->activeDaysOfWeek;
    }

    /** @param list<int>|null $days */
    public function setActiveDaysOfWeek(?array $days): void
    {
        $this->activeDaysOfWeek = $days;
        $this->touch();
    }

    public function getMaxTriggersPerCustomer(): ?int
    {
        return $this->maxTriggersPerCustomer;
    }

    public function setMaxTriggersPerCustomer(?int $max): void
    {
        $this->maxTriggersPerCustomer = $max;
        $this->touch();
    }

    public function getMaxTriggersPerPeriod(): ?int
    {
        return $this->maxTriggersPerPeriod;
    }

    public function setMaxTriggersPerPeriod(?int $max): void
    {
        $this->maxTriggersPerPeriod = $max;
        $this->touch();
    }

    public function getPeriodType(): ?string
    {
        return $this->periodType;
    }

    public function setPeriodType(?string $periodType): void
    {
        $this->periodType = $periodType;
        $this->touch();
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function isCurrentlyActive(\DateTimeImmutable $now): bool
    {
        if (!$this->isActive) {
            return false;
        }

        if (null !== $this->startsAt && $now < $this->startsAt) {
            return false;
        }

        if (null !== $this->endsAt && $now > $this->endsAt) {
            return false;
        }

        if (null !== $this->activeDaysOfWeek && [] !== $this->activeDaysOfWeek) {
            $isoDayOfWeek = (int) $now->format('N');
            if (!in_array($isoDayOfWeek % 7, array_map('intval', $this->activeDaysOfWeek), true)) {
                return false;
            }
        }

        return true;
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
