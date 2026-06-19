<?php

namespace App\State\Catalog;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Catalog\ProductFilterResource;
use App\Entity\ProductFilter;
use App\Entity\ProductFilterValue;
use App\Repository\BoutiqueRepository;
use App\Repository\ProductFilterRepository;
use App\Security\BoutiqueContext;
use App\State\Common\BoutiqueAwareProviderTrait;

/** @implements ProviderInterface<ProductFilterResource> */
final readonly class ProductFilterProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private ProductFilterRepository $filters,
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
    ) {
    }

    /** @return list<ProductFilterResource>|ProductFilterResource|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|ProductFilterResource|null
    {
        $boutique = $this->resolveBoutiqueFromRequest($context, $uriVariables);
        if (!$boutique) {
            if (!$this->context->isSuperAdmin()) {
                return $operation instanceof Get ? null : [];
            }

            if ($operation instanceof Get) {
                $filter = $this->filters->find($uriVariables['id'] ?? '');

                return $filter instanceof ProductFilter ? $this->toResource($filter) : null;
            }

            return array_map(
                fn (ProductFilter $filter): ProductFilterResource => $this->toResource($filter),
                $this->filters->findBy([], ['position' => 'ASC', 'name' => 'ASC']),
            );
        }

        if ($operation instanceof Get) {
            $filter = $this->filters->find($uriVariables['id'] ?? '');

            return $filter instanceof ProductFilter ? $this->toResource($filter) : null;
        }

        return array_map(
            fn (ProductFilter $filter): ProductFilterResource => $this->toResource($filter),
            $this->filters->findActiveByBoutique((string) $boutique->getId()),
        );
    }

    public function toResource(ProductFilter $filter): ProductFilterResource
    {
        $r = new ProductFilterResource();
        $r->id = (string) $filter->getId();
        $r->boutiqueId = (string) $filter->getBoutique()->getId();
        $r->name = $filter->getName();
        $r->slug = $filter->getSlug();
        $r->type = $filter->getType();
        $r->position = $filter->getPosition();
        $r->active = $filter->isActive();
        $r->values = array_map(
            fn (ProductFilterValue $v): array => ['id' => (string) $v->getId(), 'value' => $v->getValue()],
            $filter->getValues()->toArray(),
        );

        return $r;
    }
}
