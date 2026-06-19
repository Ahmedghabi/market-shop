<?php

namespace App\State\Catalog;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Catalog\CategoryInput;
use App\Dto\Catalog\CategoryOutput;
use App\Entity\Category;
use App\Enum\HomepageDisplayType;
use App\Repository\BoutiqueRepository;
use App\Repository\CategoryRepository;
use App\Security\BoutiqueContext;
use App\Service\FrontOfficeCacheService;
use App\Service\SeoService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\String\Slugger\AsciiSlugger;

/** @implements ProcessorInterface<CategoryInput, CategoryOutput|null> */
final readonly class CategoryProcessor implements ProcessorInterface
{
    private AsciiSlugger $slugger;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private CategoryRepository $categories,
        private EntityManagerInterface $em,
        private BoutiqueContext $context,
        private CategoryProvider $provider,
        private SeoService $seo,
        private FrontOfficeCacheService $cache,
    ) {
        $this->slugger = new AsciiSlugger();
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?CategoryOutput
    {
        unset($context);

        $boutique = $this->boutiques->findBySlugOrId((string) ($uriVariables['boutiqueId'] ?? ''));
        if (!$boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }
        if (!$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        if ($operation instanceof Delete) {
            $category = $this->categories->find((string) ($uriVariables['id'] ?? ''));
            if ($category instanceof Category) {
                $this->em->remove($category);
                $this->em->flush();
                $this->cache->invalidateSeo((string) $boutique->getId());
            }

            return null;
        }

        if (!$data instanceof CategoryInput) {
            throw new \InvalidArgumentException('Expected CategoryInput');
        }

        $category = isset($uriVariables['id']) ? $this->categories->find((string) $uriVariables['id']) : null;

        if (!$category instanceof Category) {
            $slug = $this->resolveSlug($data, $boutique, null);
            $category = new Category(
                boutique: $boutique,
                name: $data->name,
                slug: $slug,
            );
            $this->em->persist($category);
        } else {
            $slug = $this->resolveSlug($data, $boutique, (string) $category->getId());
            $category->setName($data->name);
            $category->setSlug($slug);
        }

        $category->setDescription($data->description);
        $category->setImage($data->image);
        $category->setBanner($data->banner);
        $category->setIsActive($data->isActive);
        $category->setIsFeatured($data->isFeatured);
        $category->setShowInHeader($data->showInHeader);
        $category->setShowOnHomepage($data->showOnHomepage);
        $category->setHomepageDisplayType(null !== $data->homepageDisplayType ? HomepageDisplayType::from($data->homepageDisplayType) : null);
        $category->setHomepagePosition($data->homepagePosition);
        $category->setMenuPosition($data->menuPosition);
        $category->setShowCategoryPage($data->showCategoryPage);
        $category->setProductsLimit($data->productsLimit);
        $metaTitle = $data->metaTitle ?: $this->seo->defaultMetaTitle($data->name, $boutique->getName());
        $metaDescription = $data->metaDescription ?: $this->seo->defaultMetaDescription($data->description, $data->name);
        $category->setMetaTitle($metaTitle);
        $category->setMetaDescription($metaDescription);
        $category->setMetaKeywords($data->metaKeywords);
        $category->setOgTitle($data->ogTitle ?: $metaTitle);
        $category->setOgDescription($data->ogDescription ?: $metaDescription);
        $category->setOgImage($data->ogImage ?: $this->seo->defaultOgImage($data->banner, $data->image));

        if (null !== $data->parentId) {
            $parent = $this->categories->find($data->parentId);
            if ($parent instanceof Category && (string) $parent->getBoutique()->getId() === (string) $boutique->getId()) {
                $category->setParent($parent);
            }
        } else {
            $category->setParent(null);
        }

        $this->em->flush();
        $this->cache->invalidateSeo((string) $boutique->getId());

        return $this->provider->provide(new Get(), ['boutiqueId' => (string) $boutique->getId(), 'id' => (string) $category->getId()]);
    }

    private function resolveSlug(CategoryInput $data, \App\Entity\Boutique $boutique, ?string $excludeId): string
    {
        $slug = $data->slug;

        if (null === $slug || '' === $slug) {
            $slug = $this->slugger->slug($data->name)->lower()->toString();
        }

        if (!$this->categories->slugExistsInBoutique($slug, $boutique, $excludeId)) {
            return $slug;
        }

        $counter = 2;
        while ($this->categories->slugExistsInBoutique($slug.'-'.$counter, $boutique, $excludeId)) {
            ++$counter;
        }

        return $slug.'-'.$counter;
    }
}
