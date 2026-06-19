<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'message')]
#[ORM\Index(columns: ['conversation_id'], name: 'idx_message_conversation')]
class Message extends AbstractEntity
{
    #[ORM\ManyToOne(targetEntity: Conversation::class, inversedBy: 'messages')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private Conversation $conversation;

    #[ORM\Column(length: 20)]
    private string $senderType;

    #[ORM\Column(type: Types::TEXT)]
    private string $content;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $fileUrl = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $fileType = null;

    #[ORM\Column]
    private bool $read = false;

    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $readAt = null;

    public function __construct(Conversation $conversation, string $senderType, string $content)
    {
        parent::__construct();
        $this->conversation = $conversation;
        $this->senderType = $senderType;
        $this->content = $content;
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getConversation(): Conversation
    {
        return $this->conversation;
    }

    public function getSenderType(): string
    {
        return $this->senderType;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function isRead(): bool
    {
        return $this->read;
    }

    public function markAsRead(): void
    {
        $this->read = true;
        $this->readAt = new \DateTimeImmutable();
    }

    public function getReadAt(): ?\DateTimeImmutable
    {
        return $this->readAt;
    }

    public function getFileUrl(): ?string
    {
        return $this->fileUrl;
    }

    public function setFileUrl(?string $fileUrl): void
    {
        $this->fileUrl = $fileUrl;
    }

    public function getFileType(): ?string
    {
        return $this->fileType;
    }

    public function setFileType(?string $fileType): void
    {
        $this->fileType = $fileType;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
