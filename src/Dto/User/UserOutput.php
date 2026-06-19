<?php

namespace App\Dto\User;

final class UserOutput
{
    public string $id;
    public string $email;
    public string $displayName;
    public ?string $firstname = null;
    public ?string $lastname = null;
    public ?string $phone = null;
    public array $roles;
    public string $status;
    public \DateTimeImmutable $createdAt;
    public \DateTimeImmutable $updatedAt;
    public ?\DateTimeImmutable $lastLoginAt = null;
}
