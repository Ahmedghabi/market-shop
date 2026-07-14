<?php

namespace App\State\Delivery;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Delivery\BoutiqueDeliveryAccountOutput;
use App\Entity\Boutique;
use App\Repository\BoutiqueDeliveryAccountRepository;
use App\Security\BoutiqueContext;
use App\State\Common\BoutiqueAwareProviderTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BoutiqueDeliveryAccountProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private readonly BoutiqueDeliveryAccountRepository $repository,
        private readonly BoutiqueContext $context,
    ) {
    }

    /** @return array<BoutiqueDeliveryAccountOutput>|BoutiqueDeliveryAccountOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|BoutiqueDeliveryAccountOutput|null
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
            if (!$entity || $entity->getBoutique()->getId() !== $boutique->getId()) {
                return null;
            }

            return $this->toOutput($entity);
        }

        return array_map([$this, 'toOutput'], $this->repository->findByBoutique($boutique));
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
        $output->isDefault = $entity->isDefault();
        $output->hasApiKey = null !== $entity->getEncryptedApiKey();
        $output->hasToken = null !== $entity->getEncryptedToken();
        $output->hasSecret = null !== $entity->getEncryptedSecret();
        $output->customBaseUrl = $entity->getCustomBaseUrl();
        $output->createdAt = $entity->getCreatedAt()->format('c');

        return $output;
    }
}
