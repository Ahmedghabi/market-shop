<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'conversation')]
#[ORM\Index(columns: ['boutique_id'], name: 'idx_conversation_boutique')]
#[ORM\Index(columns: ['user_id'], name: 'idx_conversation_user')]
class Conversation extends AbstractEntity
{
    #[ORM\ManyToOne(targetEntity: Boutique::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Boutique $boutique;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?User $user = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $guestName = null;

    #[ORM\Column(length: 180, nullable: true)]
    private ?string $guestEmail = null;

    #[ORM\Column(length: 64, nullable: true)]
    private ?string $guestPhone = null;

    #[ORM\Column]
    private bool $active = true;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    #[ORM\OneToMany(mappedBy: 'conversation', targetEntity: Message::class, cascade: ['persist'], orphanRemoval: true)]
    private Collection $messages;

    public function __construct(Boutique $boutique, ?User $user = null)
    {
        parent::__construct();
        $this->boutique = $boutique;
        $this->user = $user;
        $this->messages = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getBoutique(): Boutique
    {
        return $this->boutique;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function getGuestName(): ?string
    {
        return $this->guestName;
    }

    public function setGuestName(?string $guestName): void
    {
        $this->guestName = $guestName;
    }

    public function getGuestEmail(): ?string
    {
        return $this->guestEmail;
    }

    public function setGuestEmail(?string $guestEmail): void
    {
        $this->guestEmail = $guestEmail;
    }

    public function getGuestPhone(): ?string
    {
        return $this->guestPhone;
    }

    public function setGuestPhone(?string $guestPhone): void
    {
        $this->guestPhone = $guestPhone;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): void
    {
        $this->active = $active;
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

    public function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }

    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): void
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $this->touch();
        }
    }

    public function getUnreadCount(): int
    {
        $count = 0;
        foreach ($this->messages as $message) {
            if (!$message->isRead()) {
                ++$count;
            }
        }

        return $count;
    }

    public function getLastMessage(): ?Message
    {
        $last = null;
        foreach ($this->messages as $message) {
            if (null === $last || $message->getCreatedAt() > $last->getCreatedAt()) {
                $last = $message;
            }
        }

        return $last;
    }
}
