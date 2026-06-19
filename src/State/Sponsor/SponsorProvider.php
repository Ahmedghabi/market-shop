<?php

namespace App\State\Sponsor;

use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\ApiResource\Sponsor\SponsorResource;
use App\Entity\Sponsor;
use App\Repository\SponsorRepository;

/** @implements ProviderInterface<SponsorResource> */
final class SponsorProvider implements ProviderInterface
{
    public function __construct(private readonly SponsorRepository $repository)
    {
    }

    /** @return array<SponsorResource>|SponsorResource|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|SponsorResource|null
    {
        if ($operation instanceof GetCollection) {
            return array_map([$this, 'toResource'], $this->repository->findBy([], ['name' => 'ASC']));
        }

        $entity = $this->repository->find($uriVariables['id'] ?? null);

        return $entity instanceof Sponsor ? $this->toResource($entity) : null;
    }

    private function toResource(Sponsor $entity): SponsorResource
    {
        $resource = new SponsorResource();
        $resource->id = (string) $entity->getId();
        $resource->name = $entity->getName();
        $resource->scope = $entity->getScope()->value;
        $resource->logoUrl = $entity->getLogoUrl();
        $resource->targetUrl = $entity->getTargetUrl();
        $resource->active = $entity->isActive();

        return $resource;
    }
}
