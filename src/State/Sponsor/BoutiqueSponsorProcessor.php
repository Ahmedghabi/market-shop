<?php

namespace App\State\Sponsor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\ApiResource\Sponsor\BoutiqueSponsorResource;
use App\Entity\BoutiqueSponsor;
use App\Repository\BoutiqueRepository;
use App\Repository\BoutiqueSponsorRepository;
use App\Repository\SponsorRepository;
use App\Security\BoutiqueContext;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BoutiqueSponsorProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly BoutiqueRepository $boutiques,
        private readonly SponsorRepository $sponsors,
        private readonly BoutiqueSponsorRepository $repository,
        private readonly BoutiqueContext $context,
        private readonly EntityManagerInterface $em,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?BoutiqueSponsorResource
    {
        $boutique = $this->boutiques->find($uriVariables['boutiqueId'] ?? null);
        if (!$boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }
        if (!$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        if ($operation instanceof Delete) {
            $entity = $this->repository->find($uriVariables['id'] ?? null);
            if ($entity instanceof BoutiqueSponsor) {
                $this->em->remove($entity);
                $this->em->flush();
            }

            return null;
        }

        if (!$data instanceof BoutiqueSponsorResource || !$data->sponsorId) {
            throw new \InvalidArgumentException('Expected BoutiqueSponsorResource with sponsorId');
        }

        $sponsor = $this->sponsors->find($data->sponsorId);
        if (!$sponsor) {
            throw new NotFoundHttpException('Sponsor not found');
        }

        $entity = new BoutiqueSponsor($boutique, $sponsor, $data->position, $data->active);
        $this->em->persist($entity);
        $this->em->flush();

        $resource = new BoutiqueSponsorResource();
        $resource->id = (string) $entity->getId();
        $resource->boutiqueId = (string) $boutique->getId();
        $resource->sponsorId = (string) $sponsor->getId();
        $resource->name = $sponsor->getName();
        $resource->scope = $sponsor->getScope()->value;
        $resource->targetUrl = $sponsor->getTargetUrl();
        $resource->position = $entity->getPosition();
        $resource->active = $entity->isActive();

        return $resource;
    }
}
