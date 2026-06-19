<?php

namespace App\Entity;

use App\Repository\PermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PermissionRepository::class)]
#[ORM\Table(name: 'permission')]
#[ORM\UniqueConstraint(name: 'uniq_permission_code', columns: ['code'])]
class Permission extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 100)]
        private string $code,
        #[ORM\Column(length: 160)]
        private string $name,
        #[ORM\Column(length: 64)]
        private string $module,
        #[ORM\Column(type: 'text', nullable: true)]
        private ?string $description = null,
        #[ORM\Column]
        private \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
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

    public function getModule(): string
    {
        return $this->module;
    }

    public function setCode(string $code): static
    {
        $this->code = $code;

        return $this;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function setModule(string $module): static
    {
        $this->module = $module;

        return $this;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }
}
