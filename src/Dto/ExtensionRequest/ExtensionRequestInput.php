<?php

namespace App\Dto\ExtensionRequest;

use Symfony\Component\Validator\Constraints as Assert;

final class ExtensionRequestInput
{
    public ?string $boutiqueId = null;

    #[Assert\NotBlank]
    public string $extensionId;

    public ?string $comment = null;

    /**
     * Optional free-form admin comment, only used by SUPER_ADMIN decision endpoints.
     */
    public ?string $adminComment = null;
}
