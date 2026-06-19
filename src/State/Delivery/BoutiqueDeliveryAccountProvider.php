<?php

namespace App\State\Delivery;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Delivery\BoutiqueDeliveryAccountOutput;
use App\Entity\Boutique;
use App\Repository\BoutiqueDeliveryAccountRepository;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BoutiqueDeliveryAccountProvider implements ProviderInterface
{
    public function __construct(
        private readonly BoutiqueDeliveryAccountRepository $repository,
        private readonly BoutiqueRepository $boutiqueRepository,
        private readonly BoutiqueContext $context,
    ) {
    }

    /** @return array<BoutiqueDeliveryAccountOutput>|BoutiqueDeliveryAccountOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|BoutiqueDeliveryAccountOutput|null
    {
        $boutique = $this->getBoutique((string) $uriVariables['boutiqueId']);

        if (!$this->context->canAccessBoutique($boutique)) {
            return null;
        }

        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);
            if (!$entity || $entity->getBoutique()->getId() !== $boutique->getId()) {
                return null;
            }

            return $this->toOutput($entity);
        }

        return array_map([$this, 'toOutput'], $this->repository->findByBoutique($boutique));
    }

    private function getBoutique(string $id): Boutique
    {
        $entity = $this->boutiqueRepository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Boutique not found');
        }

        return $entity;
    }

    private function toOutput(object $entity): BoutiqueDeliveryAccountOutput
    {
        $output = new BoutiqueDeliveryAccountOutput();
        $output->id = (string) $entity->getId();
        $output->deliveryCompanyId = (string) $entity->getDeliveryCompany()->getId();
        $output->deliveryCompanyName = $entity->getDeliveryCompany()->getName();
        $output->isVerified = $entity->isVerified();
        $output->verifiedAt = $entity->getVerifiedAt()?->format('c');
        $output->lastError = $entity->getLastError();
        $output->isActive = $entity->isActive();
        $output->createdAt = $entity->getCreatedAt()->format('c');

        return $output;
    }
}
