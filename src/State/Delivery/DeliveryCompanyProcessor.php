<?php

namespace App\State\Delivery;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Delivery\DeliveryCompanyInput;
use App\Entity\DeliveryCompany;
use App\Enum\DeliveryAuthType;
use App\Repository\DeliveryCompanyRepository;
use App\Service\Audit\AuditLogService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class DeliveryCompanyProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly DeliveryCompanyRepository $repository,
        private readonly EntityManagerInterface $em,
        private readonly DeliveryCompanyProvider $provider,
        private readonly AuditLogService $auditLog,
        private readonly Security $security,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): mixed
    {
        if ($operation instanceof Delete) {
            $entity = $this->findEntity((string) $uriVariables['id']);
            $this->em->remove($entity);
            $this->em->flush();

            return null;
        }

        if (isset($uriVariables['id'])) {
            $entity = $this->findEntity((string) $uriVariables['id']);
            $this->applyInput($entity, $data);
            $this->audit('delivery_company.update', $entity);
        } else {
            $entity = new DeliveryCompany(
                name: $data->name,
                slug: $data->slug,
                baseUrl: $data->baseUrl,
                provider: $data->provider,
                authType: DeliveryAuthType::from($data->authType),
                authConfig: $data->authConfig,
                mappingConfig: $data->mappingConfig,
                parametersConfig: $data->parametersConfig,
                logoUrl: $data->logoUrl,
                description: $data->description,
                isActive: $data->isActive,
            );
            $this->em->persist($entity);
            $this->audit('delivery_company.create', $entity);
        }

        $this->em->flush();

        return $this->provider->toOutput($entity);
    }

    private function applyInput(DeliveryCompany $entity, DeliveryCompanyInput $input): void
    {
        $entity->setName($input->name);
        $entity->setSlug($input->slug);
        $entity->setBaseUrl($input->baseUrl);
        $entity->setProvider($input->provider);
        $entity->setAuthType(DeliveryAuthType::from($input->authType));
        $entity->setAuthConfig($input->authConfig);
        $entity->setMappingConfig($input->mappingConfig);
        $entity->setParametersConfig($input->parametersConfig);
        $entity->setLogoUrl($input->logoUrl);
        $entity->setDescription($input->description);
        $entity->setIsActive($input->isActive);
    }

    private function findEntity(string $id): DeliveryCompany
    {
        $entity = $this->repository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Delivery company not found');
        }

        return $entity;
    }

    private function audit(string $action, DeliveryCompany $company): void
    {
        $user = $this->security->getUser();
        $this->auditLog->log(
            actorEmail: $user?->getUserIdentifier() ?? 'system',
            actorRole: 'ROLE_SUPER_ADMIN',
            action: $action,
            resourceType: 'DeliveryCompany',
            resourceId: (string) $company->getId(),
            details: ['name' => $company->getName(), 'provider' => $company->getProvider()],
        );
    }
}
