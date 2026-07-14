<?php

namespace App\ApiResource\Suggestion;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Suggestion\SuggestionCommentInput;
use App\Dto\Suggestion\SuggestionCommentOutput;
use App\State\Suggestion\SuggestionCommentProcessor;
use App\State\Suggestion\SuggestionCommentProvider;

#[ApiResource(
    shortName: 'SuggestionComment',
    operations: [
        new GetCollection(uriTemplate: '/suggestions/{suggestionId}/comments', security: "is_granted('ROLE_USER')", output: SuggestionCommentOutput::class, provider: SuggestionCommentProvider::class),
        new Post(uriTemplate: '/suggestions/{suggestionId}/comments', security: "is_granted('ROLE_USER')", read: false, input: SuggestionCommentInput::class, output: SuggestionCommentOutput::class, processor: SuggestionCommentProcessor::class),
        new Patch(uriTemplate: '/suggestion-comments/{id}', security: "is_granted('ROLE_USER')", read: false, input: SuggestionCommentInput::class, output: SuggestionCommentOutput::class, processor: SuggestionCommentProcessor::class),
        new Delete(uriTemplate: '/suggestion-comments/{id}', security: "is_granted('ROLE_USER')", read: false, processor: SuggestionCommentProcessor::class),
    ],
)]
final class SuggestionCommentResource
{
}
