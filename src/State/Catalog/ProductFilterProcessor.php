<?php

namespace App\State\Catalog;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Catalog\ProductFilterResource;
use App\Entity\Boutique;
use App\Entity\ProductFilter;
use App\Repository\BoutiqueRepository;
use Doctrine\ORM\EntityManagerInterface;

/** @implements ProcessorInterface<ProductFilterResource> */
final readonly class ProductFilterProcessor implements ProcessorInterface
{
    public function __construct(
        private BoutiqueRepository $boutiques,
        private EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ProductFilterResource
    {
        $boutiqueId = $uriVariables['boutiqueId'] ?? '';

        if ($operation instanceof Delete) {
            $filter = $this->em->find(ProductFilter::class, $uriVariables['id'] ?? '');
            if ($filter instanceof ProductFilter) {
                $this->em->remove($filter);
                $this->em->flush();
            }

            return null;
        }

        $boutique = $this->boutiques->find($boutiqueId);
        if (!$boutique instanceof Boutique) {
            return null;
        }

        $filterId = $uriVariables['id'] ?? null;
        $filter = $filterId ? $this->em->find(ProductFilter::class, $filterId) : null;

        if (!$filter instanceof ProductFilter) {
            $filter = new ProductFilter($boutique, $data->name, $data->slug, $data->type);
            $this->em->persist($filter);
        }

        $filter->setName($data->name);
        $filter->setPosition($data->position);
        $filter->setActive($data->active);

        $this->em->flush();

        return (new ProductFilterProvider($this->em->getRepository(ProductFilter::class)))->provide(
            new Get(),
            ['boutiqueId' => $boutiqueId, 'id' => (string) $filter->getId()],
        );
    }
}
