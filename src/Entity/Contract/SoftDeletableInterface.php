<?php

namespace App\Entity\Contract;

interface SoftDeletableInterface
{
    public function delete(): void;

    public function restore(): void;

    public function isDeleted(): bool;

    public function getDeletedAt(): ?\DateTimeImmutable;
}
