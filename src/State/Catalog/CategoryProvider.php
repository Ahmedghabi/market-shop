<?php

namespace App\State\Catalog;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Catalog\CategoryOutput;
use App\Entity\Category;
use App\Repository\BoutiqueRepository;
use App\Repository\CategoryRepository;

/** @implements ProviderInterface<CategoryOutput> */
final readonly class CategoryProvider implements ProviderInterface
{
    public function __construct(
        private CategoryRepository $categories,
        private BoutiqueRepository $boutiques,
    ) {
    }

    /** @return list<CategoryOutput>|CategoryOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|CategoryOutput|null
    {
        unset($context);

        $boutique = $this->boutiques->findBySlugOrId((string) ($uriVariables['boutiqueId'] ?? ''));
        if (!$boutique) {
            return $operation instanceof Get ? null : [];
        }

        if ($operation instanceof Get) {
            $category = $this->categories->find((string) ($uriVariables['id'] ?? ''));

            return $category instanceof Category && (string) $category->getBoutique()->getId() === (string) $boutique->getId()
                ? $this->toOutput($category)
                : null;
        }

        return array_map(
            [$this, 'toOutput'],
            $this->categories->findByBoutique($boutique),
        );
    }

    private function toOutput(Category $category): CategoryOutput
    {
        $output = new CategoryOutput();
        $output->id = (string) $category->getId();
        $output->boutiqueId = (string) $category->getBoutique()->getId();
        $output->name = $category->getName();
        $output->slug = $category->getSlug();
        $output->parentId = null !== $category->getParent()?->getId() ? (string) $category->getParent()->getId() : null;
        $output->description = $category->getDescription();
        $output->image = $category->getImage();
        $output->banner = $category->getBanner();
        $output->isActive = $category->isActive();
        $output->isFeatured = $category->isFeatured();
        $output->showInHeader = $category->getShowInHeader();
        $output->showOnHomepage = $category->getShowOnHomepage();
        $output->homepageDisplayType = $category->getHomepageDisplayType()?->value;
        $output->homepagePosition = $category->getHomepagePosition();
        $output->menuPosition = $category->getMenuPosition();
        $output->showCategoryPage = $category->getShowCategoryPage();
        $output->productsLimit = $category->getProductsLimit();
        $output->metaTitle = $category->getMetaTitle();
        $output->metaDescription = $category->getMetaDescription();
        $output->metaKeywords = $category->getMetaKeywords();
        $output->ogTitle = $category->getOgTitle();
        $output->ogDescription = $category->getOgDescription();
        $output->ogImage = $category->getOgImage();
        $output->productsCount = $category->getProductsCount();
        $output->children = array_map(
            fn (Category $child) => [
                'id' => (string) $child->getId(),
                'name' => $child->getName(),
                'slug' => $child->getSlug(),
                'productsCount' => $child->getProductsCount(),
            ],
            $category->getChildren()->toArray(),
        );
        $output->createdAt = $category->getCreatedAt();
        $output->updatedAt = $category->getUpdatedAt();

        return $output;
    }
}
