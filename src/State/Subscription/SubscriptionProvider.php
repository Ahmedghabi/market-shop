<?php

namespace App\State\Subscription;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Subscription\SubscriptionOutput;
use App\Entity\Boutique;
use App\Entity\Subscription;
use App\Repository\BoutiqueRepository;
use App\Repository\SubscriptionRepository;
use App\Security\BoutiqueContext;
use App\State\Common\BoutiqueAwareProviderTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<SubscriptionOutput> */
final class SubscriptionProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private readonly SubscriptionRepository $repository,
        private readonly BoutiqueRepository $boutiques,
        private readonly BoutiqueContext $context,
    ) {
    }

    /** @return array<SubscriptionOutput>|SubscriptionOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|SubscriptionOutput|null
    {
        $boutique = $this->resolveBoutiqueFromRequest($context);
        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }

        if (!$this->context->canAccessBoutique($boutique)) {
            return null;
        }

        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);
            if (!$entity || (string) $entity->getBoutique()->getId() !== (string) $boutique->getId()) {
                return null;
            }

            return $this->toOutput($entity);
        }

        $entities = $this->repository->findBy(['boutique' => $boutique], ['createdAt' => 'DESC']);

        return array_map([$this, 'toOutput'], $entities);
    }

    private function toOutput(Subscription $entity): SubscriptionOutput
    {
        $output = new SubscriptionOutput();
        $output->id = (string) $entity->getId();
        $output->boutiqueId = (string) $entity->getBoutique()->getId();
        $output->boutiqueName = $entity->getBoutique()->getName();
        $output->plan = $entity->getPlan();
        $output->status = $entity->getStatus();
        $output->startDate = $entity->getStartDate()?->format('c');
        $output->endDate = $entity->getEndDate()?->format('c');
        $output->acceptedBy = $entity->getAcceptedBy();
        $output->acceptedAt = $entity->getAcceptedAt()?->format('c');
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->priceCents = $entity->getPlan()->priceCents();

        return $output;
    }
}
