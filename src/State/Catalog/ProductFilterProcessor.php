<?php

namespace App\State\Catalog;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Catalog\ProductFilterResource;
use App\Entity\ProductFilter;
use App\Repository\BoutiqueRepository;
use App\Repository\ProductFilterRepository;
use App\Security\BoutiqueContext;
use App\State\Common\BoutiqueWriteResolverTrait;
use Doctrine\ORM\EntityManagerInterface;

/** @implements ProcessorInterface<ProductFilterResource> */
final readonly class ProductFilterProcessor implements ProcessorInterface
{
    use BoutiqueWriteResolverTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private ProductFilterRepository $filters,
        private EntityManagerInterface $em,
        private BoutiqueContext $context,
        private ProductFilterProvider $provider,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ProductFilterResource
    {
        $boutique = $this->resolveBoutiqueForWrite($data, $uriVariables, $context);
        $boutiqueId = (string) $boutique->getId();

        if ($operation instanceof Delete) {
            $filter = $this->filters->find($uriVariables['id'] ?? '');
            if ($filter instanceof ProductFilter) {
                $this->em->remove($filter);
                $this->em->flush();
            }

            return null;
        }

        $filterId = $uriVariables['id'] ?? null;
        $filter = $filterId ? $this->filters->find($filterId) : null;

        if (!$filter instanceof ProductFilter) {
            $slug = $data->slug ?: trim(preg_replace('/[^a-z0-9-]+/', '-', strtolower($data->name)), '-');
            $filter = new ProductFilter($boutique, $data->name, $slug, $data->type);
            $this->em->persist($filter);
        }

        $filter->setName($data->name);
        $filter->setPosition($data->position);
        $filter->setActive($data->active);

        $this->em->flush();

        return $this->provider->toResource($filter);
    }
}
