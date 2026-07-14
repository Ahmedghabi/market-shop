<?php

namespace App\State\Catalog;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Catalog\BrandInput;
use App\Dto\Catalog\BrandOutput;
use App\Entity\Brand;
use App\Repository\BoutiqueRepository;
use App\Repository\BrandRepository;
use App\Security\BoutiqueContext;
use App\State\Common\BoutiqueWriteResolverTrait;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\AsciiSlugger;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<BrandInput, BrandOutput|null> */
final readonly class BrandProcessor implements ProcessorInterface
{
    use BoutiqueWriteResolverTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private BrandRepository $brands,
        private EntityManagerInterface $em,
        private BoutiqueContext $context,
        private BrandProvider $provider,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?BrandOutput
    {
        $boutique = $this->resolveBoutiqueForWrite($data, $uriVariables, $context);

        if ($operation instanceof Delete) {
            $brand = $this->brands->find((string) ($uriVariables['id'] ?? ''));
            if (!$brand instanceof Brand || (string) $brand->getBoutique()->getId() !== (string) $boutique->getId()) {
                throw new NotFoundHttpException('Brand not found');
            }

            $this->em->remove($brand);
            $this->em->flush();

            return null;
        }

        if (!$data instanceof BrandInput) {
            throw new \InvalidArgumentException('Expected BrandInput');
        }

        $brand = isset($uriVariables['id']) ? $this->brands->find((string) $uriVariables['id']) : null;

        if (isset($uriVariables['id']) && (!$brand instanceof Brand || (string) $brand->getBoutique()->getId() !== (string) $boutique->getId())) {
            throw new NotFoundHttpException('Brand not found');
        }

        if (!$brand instanceof Brand) {
            $slug = $this->resolveSlug($data, $boutique, null);
            $brand = new Brand(
                boutique: $boutique,
                name: $data->name,
                slug: $slug,
                logo: $data->logo,
                description: $data->description,
                website: $data->website,
                isActive: $data->isActive,
            );
            $this->em->persist($brand);
        } else {
            $slug = $this->resolveSlug($data, $boutique, (string) $brand->getId());
            $brand->setName($data->name);
            $brand->setSlug($slug);
            $brand->setLogo($data->logo);
            $brand->setDescription($data->description);
            $brand->setWebsite($data->website);
            $brand->setIsActive($data->isActive);
        }

        $this->em->flush();

        return $this->provider->provide(new Get(), ['boutiqueId' => (string) $boutique->getId(), 'id' => (string) $brand->getId()]);
    }

    private function resolveSlug(BrandInput $data, \App\Entity\Boutique $boutique, ?string $excludeId): string
    {
        $slug = $data->slug;

        if (null === $slug || '' === $slug) {
            $slug = (new AsciiSlugger())->slug($data->name)->lower()->toString();
        }

        $existing = $this->brands->findOneBy(['slug' => $slug, 'boutique' => $boutique]);
        if (!$existing instanceof Brand || (null !== $excludeId && (string) $existing->getId() === $excludeId)) {
            return $slug;
        }

        $counter = 2;
        while ($this->brands->findOneBy(['slug' => $slug.'-'.$counter, 'boutique' => $boutique])) {
            ++$counter;
        }

        return $slug.'-'.$counter;
    }
}
