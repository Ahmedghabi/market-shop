<?php

namespace App\State\Extension;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Entity\Boutique;
use App\Entity\Extension;
use App\Enum\ExtensionRequestStatus;
use App\Dto\Extension\ExtensionOutput;
use App\Repository\BoutiqueExtensionRepository;
use App\Repository\BoutiqueRepository;
use App\Repository\ExtensionRepository;
use App\Repository\ExtensionRequestRepository;
use App\Security\BoutiqueContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProviderInterface<ExtensionOutput> */
final class ExtensionProvider implements ProviderInterface
{
    public function __construct(
        private readonly ExtensionRepository $repository,
        private readonly BoutiqueExtensionRepository $boutiqueExtensions,
        private readonly ExtensionRequestRepository $extensionRequests,
        private readonly BoutiqueRepository $boutiques,
        private readonly BoutiqueContext $context,
    ) {
    }

    /** @return array<ExtensionOutput>|ExtensionOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|ExtensionOutput|null
    {
        $operationName = $operation->getName() ?? '';

        if ('available_extensions' === $operationName) {
            return $this->provideAvailable($context);
        }

        if (isset($uriVariables['id'])) {
            $entity = $this->repository->find($uriVariables['id']);
            if (!$entity) {
                throw new NotFoundHttpException('Extension not found');
            }

            return $this->toOutput($entity);
        }

        $entities = $this->repository->findBy([], ['name' => 'ASC']);

        return array_map([$this, 'toOutput'], $entities);
    }

    /** @return ExtensionOutput[] */
    private function provideAvailable(array $context): array
    {
        $boutique = $this->resolveBoutique($context);
        if (!$boutique instanceof Boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }

        $activeExtensionIds = array_map(
            static fn ($grant) => (string) $grant->getExtension()->getId(),
            $this->boutiqueExtensions->findActiveByBoutique($boutique),
        );

        $pendingByExtension = [];
        foreach ($this->extensionRequests->findByBoutique($boutique) as $request) {
            $extId = (string) $request->getExtension()->getId();
            if (\in_array($request->getStatus(), [
                ExtensionRequestStatus::Rejected,
                ExtensionRequestStatus::Cancelled,
                ExtensionRequestStatus::Expired,
            ], true)) {
                continue;
            }
            if (!isset($pendingByExtension[$extId])) {
                $pendingByExtension[$extId] = $request;
            }
        }

        $result = [];
        foreach ($this->repository->findAllActive() as $extension) {
            $extId = (string) $extension->getId();
            $output = $this->toOutput($extension);
            $output->alreadyActive = \in_array($extId, $activeExtensionIds, true);
            if (isset($pendingByExtension[$extId])) {
                $output->pendingRequestId = (string) $pendingByExtension[$extId]->getId();
                $output->pendingRequestStatus = $pendingByExtension[$extId]->getStatus()->value;
            }
            $result[] = $output;
        }

        return $result;
    }

    private function resolveBoutique(array $context): ?Boutique
    {
        $request = $context['request'] ?? null;
        $boutique = $request instanceof Request ? $request->attributes->get('_boutique') : null;
        if ($boutique instanceof Boutique) {
            return $boutique;
        }

        $queryBoutiqueId = $request?->query->get('boutiqueId');
        if (\is_string($queryBoutiqueId) && '' !== $queryBoutiqueId && $this->context->isSuperAdmin()) {
            return $this->boutiques->find($queryBoutiqueId);
        }

        $boutiqueId = $this->context->getBoutiqueId();

        return null !== $boutiqueId ? $this->boutiques->find((string) $boutiqueId) : null;
    }

    public function toOutput(Extension $entity): ExtensionOutput
    {
        $output = new ExtensionOutput();
        $output->id = (string) $entity->getId();
        $output->code = $entity->getCode();
        $output->name = $entity->getName();
        $output->description = $entity->getDescription();
        $output->type = $entity->getType()->value;
        $output->targetCode = $entity->getTargetCode();
        $output->value = $entity->getValue();
        $output->priceTnd = $entity->getPriceTnd();
        $output->durationMonths = $entity->getDurationMonths();
        $output->requiresValidation = $entity->requiresValidation();
        $output->isActive = $entity->isActive();
        $output->icon = $entity->getIcon();
        $output->isFree = $entity->isFree();
        $output->isPermanent = $entity->isPermanent();
        $output->createdAt = $entity->getCreatedAt()->format('c');
        $output->updatedAt = $entity->getUpdatedAt()?->format('c');

        return $output;
    }
}
