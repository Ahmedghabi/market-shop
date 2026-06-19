<?php

namespace App\Entity;

use App\Doctrine\Traits\SoftDeleteTrait;
use App\Entity\Contract\SoftDeletableInterface;
use App\Enum\WebhookEventType;
use App\Repository\WebhookRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: WebhookRepository::class)]
#[ORM\Table(name: 'webhook')]
class Webhook extends AbstractEntity implements SoftDeletableInterface
{
    use SoftDeleteTrait;

    public function __construct(
        #[ORM\ManyToOne(targetEntity: Boutique::class)]
        #[ORM\JoinColumn(nullable: true, onDelete: 'CASCADE')]
        private ?Boutique $boutique = null,
        #[ORM\Column(length: 255)]
        private string $url,
        #[ORM\Column(type: 'json')]
        private array $events = [],
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $secret = null,
        #[ORM\Column(length: 32)]
        private string $status = 'ACTIVE',
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $lastTriggeredAt = null,
        #[ORM\Column(nullable: true)]
        private ?int $failureCount = 0,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column(nullable: true)]
        private ?\DateTimeImmutable $updatedAt = null,
    ) {
        parent::__construct();
    }

    public function getBoutique(): ?Boutique
    {
        return $this->boutique;
    }

    public function getUrl(): string
    {
        return $this->url;
    }

    public function setUrl(string $url): void
    {
        $this->url = $url;
        $this->touch();
    }

    public function getEvents(): array
    {
        return $this->events;
    }

    public function setEvents(array $events): void
    {
        $this->events = $events;
        $this->touch();
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(?string $secret): void
    {
        $this->secret = $secret;
        $this->touch();
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): void
    {
        $this->status = $status;
        $this->touch();
    }

    public function isListeningTo(WebhookEventType $event): bool
    {
        return in_array($event->value, $this->events, true);
    }

    public function getLastTriggeredAt(): ?\DateTimeImmutable
    {
        return $this->lastTriggeredAt;
    }

    public function markTriggered(): void
    {
        $this->lastTriggeredAt = new \DateTimeImmutable();
        $this->failureCount = 0;
        $this->touch();
    }

    public function markFailed(): void
    {
        ++$this->failureCount;
        $this->touch();

        if ($this->failureCount >= 10) {
            $this->status = 'DISABLED';
        }
    }

    public function getFailureCount(): int
    {
        return $this->failureCount ?? 0;
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
