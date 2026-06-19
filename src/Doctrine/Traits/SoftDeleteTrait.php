<?php

namespace App\Doctrine\Traits;

use Doctrine\ORM\Mapping as ORM;

trait SoftDeleteTrait
{
    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $deletedAt = null;

    public function delete(): void
    {
        if (null === $this->deletedAt) {
            $this->deletedAt = new \DateTimeImmutable();
        }
    }

    public function restore(): void
    {
        $this->deletedAt = null;
    }

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }
}
