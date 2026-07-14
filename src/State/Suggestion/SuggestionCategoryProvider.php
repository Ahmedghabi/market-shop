<?php

namespace App\State\Suggestion;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\SuggestionCategory;
use App\Repository\SuggestionCategoryRepository;
use App\Service\Suggestion\SuggestionAccessService;
use App\Service\Suggestion\SuggestionOutputMapper;

final readonly class SuggestionCategoryProvider implements ProviderInterface
{
    public function __construct(private SuggestionCategoryRepository $categories, private SuggestionAccessService $access, private SuggestionOutputMapper $mapper)
    {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): object|array|null
    {
        $admin = str_starts_with($operation->getUriTemplate() ?? '', '/admin/');
        if ($admin) {
            $this->access->assertPermission('suggestion.category.manage');
        }
        if (isset($uriVariables['id'])) {
            $category = $this->categories->find($uriVariables['id']);

            return $category instanceof SuggestionCategory ? $this->mapper->category($category) : null;
        }
        $categories = $admin ? $this->categories->findAll() : $this->categories->findActiveOrdered();

        return array_map(fn (SuggestionCategory $category) => $this->mapper->category($category), $categories);
    }
}
