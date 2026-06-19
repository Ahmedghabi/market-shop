<?php

namespace App\Dto\UserShop;

final class UserShopOutput
{
    public string $id;
    public string $userId;
    public string $boutiqueId;
    public string $boutiqueName;
    public string $role;
    public string $status;
    public \DateTimeImmutable $createdAt;
}
