<?php

namespace App\ApiResource\Suggestion;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use App\Dto\Suggestion\SuggestionInput;
use App\Dto\Suggestion\SuggestionOfficialResponseInput;
use App\Dto\Suggestion\SuggestionOutput;
use App\Dto\Suggestion\SuggestionStatusInput;
use App\Dto\Suggestion\SuggestionVisibilityInput;
use App\State\Suggestion\SuggestionProcessor;
use App\State\Suggestion\SuggestionProvider;

#[ApiResource(
    shortName: 'Suggestion',
    operations: [
        new GetCollection(uriTemplate: '/suggestions', security: "is_granted('ROLE_USER')", output: SuggestionOutput::class, provider: SuggestionProvider::class),
        new Get(uriTemplate: '/suggestions/{id}', security: "is_granted('ROLE_USER')", output: SuggestionOutput::class, provider: SuggestionProvider::class),
        new Post(uriTemplate: '/suggestions', security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN')", read: false, input: SuggestionInput::class, output: SuggestionOutput::class, processor: SuggestionProcessor::class),
        new Patch(uriTemplate: '/suggestions/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CUSTOMER')", read: false, input: SuggestionInput::class, output: SuggestionOutput::class, processor: SuggestionProcessor::class),
        new Put(uriTemplate: '/suggestions/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CUSTOMER')", read: false, input: SuggestionInput::class, output: SuggestionOutput::class, processor: SuggestionProcessor::class),
        new Delete(uriTemplate: '/suggestions/{id}', security: "is_granted('ROLE_BOUTIQUE_ADMIN') or is_granted('ROLE_SUPER_ADMIN') or is_granted('ROLE_CUSTOMER')", read: false, processor: SuggestionProcessor::class),
        new GetCollection(uriTemplate: '/public/suggestions', security: "is_granted('PUBLIC_ACCESS')", output: SuggestionOutput::class, provider: SuggestionProvider::class, name: 'public_suggestions'),
        new Get(uriTemplate: '/public/suggestions/{id}', security: "is_granted('PUBLIC_ACCESS')", output: SuggestionOutput::class, provider: SuggestionProvider::class, name: 'public_suggestion'),
        new Patch(name: 'suggestion_status', uriTemplate: '/admin/suggestions/{id}/status', security: "is_granted('ROLE_USER')", read: false, input: SuggestionStatusInput::class, output: SuggestionOutput::class, processor: SuggestionProcessor::class),
        new Patch(name: 'suggestion_visibility', uriTemplate: '/admin/suggestions/{id}/visibility', security: "is_granted('ROLE_USER')", read: false, input: SuggestionVisibilityInput::class, output: SuggestionOutput::class, processor: SuggestionProcessor::class),
        new Post(name: 'suggestion_publish', uriTemplate: '/admin/suggestions/{id}/publish', security: "is_granted('ROLE_USER')", read: false, input: false, output: SuggestionOutput::class, processor: SuggestionProcessor::class),
        new Post(name: 'suggestion_official_response', uriTemplate: '/admin/suggestions/{id}/official-response', security: "is_granted('ROLE_USER')", read: false, input: SuggestionOfficialResponseInput::class, output: SuggestionOutput::class, processor: SuggestionProcessor::class),
        new Post(name: 'suggestion_archive', uriTemplate: '/admin/suggestions/{id}/archive', security: "is_granted('ROLE_USER')", read: false, input: false, output: SuggestionOutput::class, processor: SuggestionProcessor::class),
    ],
    paginationItemsPerPage: 30,
)]
final class SuggestionResource
{
}
