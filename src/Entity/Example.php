<?php

namespace App\Entity;

use App\Enum\ExampleStatus;
use App\Repository\ExampleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExampleRepository::class)]
#[ORM\Table(name: 'example')]
class Example extends AbstractEntity
{
    #[ORM\Column(length: 120)]
    private string $name;

    #[ORM\Column(length: 32, enumType: ExampleStatus::class)]
    private ExampleStatus $status = ExampleStatus::Draft;

    public function __construct(string $name)
    {
        parent::__construct();
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): ExampleStatus
    {
        return $this->status;
    }

    public function publish(): void
    {
        $this->status = ExampleStatus::Published;
    }
}
