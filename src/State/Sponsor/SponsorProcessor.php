<?php

namespace App\State\Sponsor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Sponsor\SponsorResource;
use App\Entity\Sponsor;
use App\Enum\SponsorScope;
use App\Repository\SponsorRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class SponsorProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly SponsorRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?SponsorResource
    {
        if ($operation instanceof Delete) {
            $entity = $this->findSponsor((string) ($uriVariables['id'] ?? ''));
            $this->em->remove($entity);
            $this->em->flush();

            return null;
        }

        if (!$data instanceof SponsorResource) {
            throw new \InvalidArgumentException('Expected SponsorResource');
        }

        $entity = isset($uriVariables['id']) ? $this->findSponsor((string) $uriVariables['id']) : new Sponsor($data->name ?? '', SponsorScope::from($data->scope));
        $entity->setName($data->name ?? '');
        $entity->setScope(SponsorScope::from($data->scope));
        $entity->setLogoUrl($data->logoUrl);
        $entity->setTargetUrl($data->targetUrl);
        $entity->setActive($data->active);

        $this->em->persist($entity);
        $this->em->flush();

        $resource = new SponsorResource();
        $resource->id = (string) $entity->getId();
        $resource->name = $entity->getName();
        $resource->scope = $entity->getScope()->value;
        $resource->logoUrl = $entity->getLogoUrl();
        $resource->targetUrl = $entity->getTargetUrl();
        $resource->active = $entity->isActive();

        return $resource;
    }

    private function findSponsor(string $id): Sponsor
    {
        $entity = $this->repository->find($id);
        if (!$entity instanceof Sponsor) {
            throw new NotFoundHttpException('Sponsor not found');
        }

        return $entity;
    }
}
