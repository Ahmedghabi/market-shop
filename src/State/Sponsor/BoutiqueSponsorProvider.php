<?php

namespace App\State\Sponsor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Sponsor\BoutiqueSponsorResource;
use App\Entity\Boutique;
use App\Repository\BoutiqueSponsorRepository;
use App\Security\BoutiqueContext;
use App\State\Common\BoutiqueAwareProviderTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<BoutiqueSponsorResource> */
final class BoutiqueSponsorProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private readonly BoutiqueSponsorRepository $repository,
        private readonly BoutiqueContext $context,
    ) {
    }

    /** @return array<BoutiqueSponsorResource> */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array
    {
        $boutique = $this->resolveBoutiqueFromRequest($context);
        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }
        if (!$this->context->canAccessBoutique($boutique)) {
            return [];
        }

        return array_map([$this, 'toResource'], $this->repository->findBy(['boutique' => $boutique], ['position' => 'ASC']));
    }

    private function toResource(BoutiqueSponsor $entity): BoutiqueSponsorResource
    {
        $resource = new BoutiqueSponsorResource();
        $resource->id = (string) $entity->getId();
        $resource->boutiqueId = (string) $entity->getBoutique()->getId();
        $resource->sponsorId = (string) $entity->getSponsor()->getId();
        $resource->name = $entity->getSponsor()->getName();
        $resource->scope = $entity->getSponsor()->getScope()->value;
        $resource->targetUrl = $entity->getSponsor()->getTargetUrl();
        $resource->position = $entity->getPosition();
        $resource->active = $entity->isActive();

        return $resource;
    }
}
