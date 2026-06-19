<?php

namespace App\Entity;

use App\Repository\RolePermissionRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RolePermissionRepository::class)]
#[ORM\Table(name: 'role_permission')]
#[ORM\UniqueConstraint(name: 'uniq_role_permission', columns: ['role_code', 'permission'])]
class RolePermission extends AbstractEntity
{
    public function __construct(
        #[ORM\Column(length: 60)]
        private string $roleCode,
        #[ORM\Column(length: 100)]
        private string $permission,
        #[ORM\Column(length: 255, nullable: true)]
        private ?string $description = null,
    ) {
        parent::__construct();
    }

    public function getRoleCode(): string
    {
        return $this->roleCode;
    }

    public function getPermission(): string
    {
        return $this->permission;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
