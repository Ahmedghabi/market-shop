<?php

namespace App\State\SubscriptionRequest;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\SubscriptionRequest\SubscriptionRequestOutput;
use App\Entity\Boutique;
use App\Entity\SubscriptionRequest;
use App\Repository\SubscriptionRequestRepository;
use App\Security\BoutiqueContext;
use App\State\Common\BoutiqueAwareProviderTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<SubscriptionRequestOutput> */
final class SubscriptionRequestProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private readonly SubscriptionRequestRepository $repository,
        private readonly BoutiqueContext $context,
    ) {
    }

    /** @return array<SubscriptionRequestOutput>|SubscriptionRequestOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|SubscriptionRequestOutput|null
    {
        if ($this->context->isSuperAdmin() && str_starts_with($operation->getUriTemplate() ?? '', '/admin/')) {
            if (isset($uriVariables['id'])) {
                $entity = $this->repository->find($uriVariables['id']);

                return $entity ? $this->toOutput($entity) : null;
            }

            $entities = $this->repository->findBy([], ['requestedAt' => 'DESC']);

            return array_map([$this, 'toOutput'], $entities);
        }

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

        $entities = $this->repository->findBy(['boutique' => $boutique], ['requestedAt' => 'DESC']);

        return array_map([$this, 'toOutput'], $entities);
    }

    private function toOutput(SubscriptionRequest $entity): SubscriptionRequestOutput
    {
        $output = new SubscriptionRequestOutput();
        $output->id = (string) $entity->getId();
        $output->boutiqueId = (string) $entity->getBoutique()->getId();
        $output->boutiqueName = $entity->getBoutique()->getName();
        $output->subscriptionPlanId = (string) $entity->getSubscriptionPlan()->getId();
        $output->subscriptionPlanName = $entity->getSubscriptionPlan()->getName();
        $output->status = $entity->getStatus()->value;
        $output->requestedAt = $entity->getRequestedAt()->format('c');
        $output->approvedAt = $entity->getApprovedAt()?->format('c');
        $output->approvedBy = $entity->getApprovedBy();

        return $output;
    }
}
