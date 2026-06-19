<?php

namespace App\Doctrine\Traits;

use Doctrine\ORM\Mapping as ORM;

trait EnabledFieldTrait
{
    #[ORM\Column]
    private bool $enabled = true;

    public function isEnabled(): bool
    {
        return $this->enabled;
    }
}
