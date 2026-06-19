<?php

namespace App\State\Catalog;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Catalog\BrandOutput;
use App\Entity\Brand;
use App\Repository\BoutiqueRepository;
use App\Repository\BrandRepository;

/** @implements ProviderInterface<BrandOutput> */
final readonly class BrandProvider implements ProviderInterface
{
    public function __construct(
        private BrandRepository $brands,
        private BoutiqueRepository $boutiques,
    ) {
    }

    /** @return list<BrandOutput>|BrandOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|BrandOutput|null
    {
        unset($context);

        $boutique = $this->boutiques->findBySlugOrId((string) ($uriVariables['boutiqueId'] ?? ''));
        if (!$boutique) {
            return $operation instanceof Get ? null : [];
        }

        if ($operation instanceof Get) {
            $brand = $this->brands->find((string) ($uriVariables['id'] ?? ''));

            return $brand instanceof Brand && (string) $brand->getBoutique()->getId() === (string) $boutique->getId()
                ? $this->toOutput($brand)
                : null;
        }

        return array_map(
            [$this, 'toOutput'],
            $this->brands->findByBoutique($boutique),
        );
    }

    private function toOutput(Brand $brand): BrandOutput
    {
        $output = new BrandOutput();
        $output->id = (string) $brand->getId();
        $output->boutiqueId = (string) $brand->getBoutique()->getId();
        $output->name = $brand->getName();
        $output->slug = $brand->getSlug();
        $output->logo = $brand->getLogo();
        $output->description = $brand->getDescription();
        $output->website = $brand->getWebsite();
        $output->isActive = $brand->isActive();
        $output->productsCount = $brand->getProductsCount();
        $output->createdAt = $brand->getCreatedAt();
        $output->updatedAt = $brand->getUpdatedAt();

        return $output;
    }
}
