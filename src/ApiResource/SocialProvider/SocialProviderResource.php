<?php

namespace App\ApiResource\SocialProvider;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\SocialProvider\SocialProviderInput;
use App\Dto\SocialProvider\SocialProviderOutput;
use App\State\SocialProvider\SocialProviderProvider;
use App\State\SocialProvider\SocialProviderProcessor;

#[ApiResource(
    shortName: 'SocialProvider',
    operations: [
        new GetCollection(
            uriTemplate: '/admin/social-providers',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: SocialProviderOutput::class,
            provider: SocialProviderProvider::class,
        ),
        new Post(
            uriTemplate: '/admin/social-providers',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: SocialProviderInput::class,
            output: SocialProviderOutput::class,
            processor: SocialProviderProcessor::class,
        ),
        new Get(
            uriTemplate: '/admin/social-providers/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            output: SocialProviderOutput::class,
            provider: SocialProviderProvider::class,
        ),
        new Patch(
            uriTemplate: '/admin/social-providers/{id}',
            security: "is_granted('ROLE_SUPER_ADMIN')",
            input: SocialProviderInput::class,
            output: SocialProviderOutput::class,
            processor: SocialProviderProcessor::class,
        ),
    ],
)]
final class SocialProviderResource
{
    public ?string $id = null;
    public ?string $code = null;
    public ?string $name = null;
    public bool $isActive = false;
}
