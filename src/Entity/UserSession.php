<?php

namespace App\Entity;

use App\Enum\SessionDeviceType;
use App\Repository\UserSessionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSessionRepository::class)]
#[ORM\Table(name: 'user_session')]
#[ORM\UniqueConstraint(name: 'uniq_user_session_token', columns: ['token_id'])]
#[ORM\Index(name: 'idx_user_session_user', columns: ['user_id'])]
#[ORM\Index(name: 'idx_user_session_expires', columns: ['expires_at'])]
class UserSession extends AbstractEntity
{
    public function __construct(
        #[ORM\ManyToOne(targetEntity: User::class)]
        #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
        private User $user,
        #[ORM\Column(length: 64, unique: true)]
        private string $tokenId,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $deviceName = null,
        #[ORM\Column(length: 16, enumType: SessionDeviceType::class)]
        private SessionDeviceType $deviceType = SessionDeviceType::Unknown,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $browser = null,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $operatingSystem = null,
        #[ORM\Column(length: 64, nullable: true)]
        private ?string $ipAddress = null,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $country = null,
        #[ORM\Column(length: 120, nullable: true)]
        private ?string $city = null,
        #[ORM\Column]
        private \DateTimeImmutable $lastActivityAt = new \DateTimeImmutable(),
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
        #[ORM\Column]
        private \DateTimeImmutable $expiresAt = new \DateTimeImmutable('+30 days'),
        #[ORM\Column]
        private bool $isCurrent = true,
        #[ORM\Column]
        private bool $isActive = true,
    ) {
        parent::__construct();
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getTokenId(): string
    {
        return $this->tokenId;
    }

    public function getDeviceName(): ?string
    {
        return $this->deviceName;
    }

    public function getDeviceType(): SessionDeviceType
    {
        return $this->deviceType;
    }

    public function getBrowser(): ?string
    {
        return $this->browser;
    }

    public function getOperatingSystem(): ?string
    {
        return $this->operatingSystem;
    }

    public function getIpAddress(): ?string
    {
        return $this->ipAddress;
    }

    public function getCountry(): ?string
    {
        return $this->country;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function getLastActivityAt(): \DateTimeImmutable
    {
        return $this->lastActivityAt;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getExpiresAt(): \DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function isCurrent(): bool
    {
        return $this->isCurrent;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function markCurrent(bool $current): void
    {
        $this->isCurrent = $current;
    }

    public function touch(?\DateTimeImmutable $at = null): void
    {
        $this->lastActivityAt = $at ?? new \DateTimeImmutable();
    }

    public function deactivate(): void
    {
        $this->isActive = false;
        $this->isCurrent = false;
    }

    public function isExpiredAt(\DateTimeImmutable $now): bool
    {
        return $this->expiresAt <= $now;
    }
}
