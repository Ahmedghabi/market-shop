<?php

namespace App\Doctrine\Traits;

use Doctrine\ORM\Mapping as ORM;

trait DateTimeFieldTrait
{
    #[ORM\Column]
    private \DateTimeImmutable $createdAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    public function markCreated(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function markUpdated(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
