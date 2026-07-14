<?php

namespace App\State\ExtensionRequest;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\ExtensionRequest\ExtensionRequestOutput;
use App\Entity\Boutique;
use App\Entity\ExtensionRequest;
use App\Repository\ExtensionRequestRepository;
use App\Security\BoutiqueContext;
use App\State\Common\BoutiqueAwareProviderTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ExtensionRequestOutput> */
final class ExtensionRequestProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private readonly ExtensionRequestRepository $repository,
        private readonly BoutiqueContext $context,
    ) {
    }

    /** @return array<ExtensionRequestOutput>|ExtensionRequestOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|ExtensionRequestOutput|null
    {
        if ($this->context->isSuperAdmin() && str_starts_with($operation->getUriTemplate() ?? '', '/admin/')) {
            if (isset($uriVariables['id'])) {
                $entity = $this->repository->find($uriVariables['id']);

                return $entity ? $this->toOutput($entity) : null;
            }

            return array_map([$this, 'toOutput'], $this->repository->findAllOrdered());
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

        return array_map([$this, 'toOutput'], $this->repository->findByBoutique($boutique));
    }

    public function toOutput(ExtensionRequest $entity): ExtensionRequestOutput
    {
        $output = new ExtensionRequestOutput();
        $output->id = (string) $entity->getId();
        $output->boutiqueId = (string) $entity->getBoutique()->getId();
        $output->boutiqueName = $entity->getBoutique()->getName();
        $output->extensionId = (string) $entity->getExtension()->getId();
        $output->extensionCode = $entity->getExtension()->getCode();
        $output->extensionName = $entity->getExtension()->getName();
        $output->extensionType = $entity->getExtension()->getType()->value;
        $output->priceTnd = $entity->getPriceTnd();
        $output->status = $entity->getStatus()->value;
        $output->comment = $entity->getComment();
        $output->adminComment = $entity->getAdminComment();
        $output->invoiceId = $entity->getInvoice() ? (string) $entity->getInvoice()->getId() : null;
        $output->requestedAt = $entity->getRequestedAt()->format('c');
        $output->paidAt = $entity->getPaidAt()?->format('c');
        $output->decidedAt = $entity->getDecidedAt()?->format('c');
        $output->decidedBy = $entity->getDecidedBy();
        $output->grantId = $entity->getGrant() ? (string) $entity->getGrant()->getId() : null;
        $output->grantExpiresAt = $entity->getGrant()?->getExpiresAt()?->format('c');

        return $output;
    }
}
