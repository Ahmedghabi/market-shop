<?php

namespace App\ApiResource\Suggestion;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\Metadata\Post;
use App\Dto\Suggestion\SuggestionCategoryInput;
use App\Dto\Suggestion\SuggestionCategoryOutput;
use App\State\Suggestion\SuggestionCategoryProcessor;
use App\State\Suggestion\SuggestionCategoryProvider;

#[ApiResource(
    shortName: 'SuggestionCategory',
    operations: [
        new GetCollection(uriTemplate: '/public/suggestion-categories', security: "is_granted('PUBLIC_ACCESS')", output: SuggestionCategoryOutput::class, provider: SuggestionCategoryProvider::class, name: 'public_suggestion_categories'),
        new Get(uriTemplate: '/admin/suggestion-categories/{id}', security: "is_granted('ROLE_SUPER_ADMIN')", output: SuggestionCategoryOutput::class, provider: SuggestionCategoryProvider::class),
        new GetCollection(uriTemplate: '/admin/suggestion-categories', security: "is_granted('ROLE_SUPER_ADMIN')", output: SuggestionCategoryOutput::class, provider: SuggestionCategoryProvider::class),
        new Post(uriTemplate: '/admin/suggestion-categories', security: "is_granted('ROLE_SUPER_ADMIN')", read: false, input: SuggestionCategoryInput::class, output: SuggestionCategoryOutput::class, processor: SuggestionCategoryProcessor::class),
        new Patch(uriTemplate: '/admin/suggestion-categories/{id}', security: "is_granted('ROLE_SUPER_ADMIN')", read: false, input: SuggestionCategoryInput::class, output: SuggestionCategoryOutput::class, processor: SuggestionCategoryProcessor::class),
        new Delete(uriTemplate: '/admin/suggestion-categories/{id}', security: "is_granted('ROLE_SUPER_ADMIN')", read: false, processor: SuggestionCategoryProcessor::class),
    ],
)]
final class SuggestionCategoryResource
{
}
