<?php

namespace App\ApiResource\Suggestion;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Suggestion\SuggestionReactionInput;
use App\Dto\Suggestion\SuggestionReactionOutput;
use App\State\Suggestion\SuggestionReactionProcessor;
use App\State\Suggestion\SuggestionReactionProvider;

#[ApiResource(
    shortName: 'SuggestionReaction',
    operations: [
        new GetCollection(uriTemplate: '/suggestions/{suggestionId}/reactions', security: "is_granted('ROLE_USER')", output: SuggestionReactionOutput::class, provider: SuggestionReactionProvider::class),
        new Get(uriTemplate: '/suggestions/{suggestionId}/reactions/{id}', security: "is_granted('ROLE_USER')", output: SuggestionReactionOutput::class, provider: SuggestionReactionProvider::class),
        new Post(uriTemplate: '/suggestions/{suggestionId}/reactions', security: "is_granted('ROLE_USER')", read: false, input: SuggestionReactionInput::class, output: SuggestionReactionOutput::class, processor: SuggestionReactionProcessor::class),
        new Patch(uriTemplate: '/suggestions/{suggestionId}/reactions', security: "is_granted('ROLE_USER')", read: false, input: SuggestionReactionInput::class, output: SuggestionReactionOutput::class, processor: SuggestionReactionProcessor::class),
        new Delete(uriTemplate: '/suggestions/{suggestionId}/reactions', security: "is_granted('ROLE_USER')", read: false, processor: SuggestionReactionProcessor::class),
    ],
)]
final class SuggestionReactionResource
{
}
