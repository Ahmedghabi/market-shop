<?php

namespace App\ApiResource\Boutique;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\State\Boutique\SettingsProvider;
use App\State\Boutique\SettingsProcessor;

#[ApiResource(
    shortName: 'BoutiqueSettings',
    operations: [
        new Get(uriTemplate: '/boutiques/{boutiqueId}/settings', output: \App\Dto\Boutique\BoutiqueSettingsOutput::class, provider: SettingsProvider::class),
        new Patch(uriTemplate: '/boutiques/{boutiqueId}/settings', security: "is_granted('ROLE_BOUTIQUE_ADMIN')", input: \App\Dto\Boutique\BoutiqueSettingsInput::class, processor: SettingsProcessor::class),
    ],
)]
final class BoutiqueSettingsResource
{
    public ?string $boutiqueId = null;
}
