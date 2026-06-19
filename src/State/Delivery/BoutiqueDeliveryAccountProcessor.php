<?php

namespace App\State\Delivery;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Delivery\BoutiqueDeliveryAccountInput;
use App\Dto\Delivery\BoutiqueDeliveryAccountOutput;
use App\Entity\Boutique;
use App\Entity\BoutiqueDeliveryAccount;
use App\Repository\BoutiqueDeliveryAccountRepository;
use App\Repository\BoutiqueRepository;
use App\Repository\DeliveryCompanyRepository;
use App\Security\BoutiqueContext;
use App\Service\Delivery\DeliveryApiClient;
use App\Service\Delivery\EncryptionService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

final class BoutiqueDeliveryAccountProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly BoutiqueDeliveryAccountRepository $repository,
        private readonly BoutiqueRepository $boutiqueRepository,
        private readonly DeliveryCompanyRepository $companyRepository,
        private readonly EntityManagerInterface $em,
        private readonly BoutiqueContext $context,
        private readonly EncryptionService $encryption,
        private readonly DeliveryApiClient $apiClient,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?BoutiqueDeliveryAccountOutput
    {
        $boutique = $this->getBoutique((string) $uriVariables['boutiqueId']);

        if (!$this->context->canAccessBoutique($boutique)) {
            throw new \Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException('Access denied');
        }

        $operationName = $operation->getName() ?? '';

        if ('verify_delivery_account' === $operationName) {
            return $this->verifyAccount($boutique, (string) $uriVariables['id']);
        }

        if ($operation instanceof Delete) {
            $entity = $this->findAccount($boutique, (string) $uriVariables['id']);
            $this->em->remove($entity);
            $this->em->flush();

            return null;
        }

        if (isset($uriVariables['id'])) {
            $entity = $this->findAccount($boutique, (string) $uriVariables['id']);
            $this->applyInput($entity, $data, $boutique);
        } else {
            $company = $this->companyRepository->find($data->deliveryCompanyId);
            if (!$company) {
                throw new NotFoundHttpException('Delivery company not found');
            }

            $encryptedLogin = $this->encryption->encrypt($data->login);
            $encryptedPassword = $this->encryption->encrypt($data->password);

            $entity = new BoutiqueDeliveryAccount(
                boutique: $boutique,
                deliveryCompany: $company,
                encryptedLogin: $encryptedLogin,
                encryptedPassword: $encryptedPassword,
            );
            $this->em->persist($entity);
        }

        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function applyInput(BoutiqueDeliveryAccount $entity, BoutiqueDeliveryAccountInput $input, Boutique $boutique): void
    {
        if ($input->deliveryCompanyId) {
            $company = $this->companyRepository->find($input->deliveryCompanyId);
            if (!$company) {
                throw new NotFoundHttpException('Delivery company not found');
            }
            // NOTE: deliveryCompany cannot be changed after creation in this simple implementation
        }

        if ($input->login || $input->password) {
            $login = $input->login ?: $this->encryption->decrypt($entity->getEncryptedLogin());
            $password = $input->password ?: $this->encryption->decrypt($entity->getEncryptedPassword());
            $entity->setEncryptedCredentials(
                $this->encryption->encrypt($login),
                $this->encryption->encrypt($password),
            );
            $entity->markAsUnverified('Identifiants modifiés, vérification requise');
        }
    }

    private function verifyAccount(Boutique $boutique, string $id): BoutiqueDeliveryAccountOutput
    {
        $entity = $this->findAccount($boutique, $id);

        try {
            $login = $this->encryption->decrypt($entity->getEncryptedLogin());
            $password = $this->encryption->decrypt($entity->getEncryptedPassword());
        } catch (\RuntimeException $e) {
            $entity->markAsUnverified('Erreur de déchiffrement');
            $this->em->flush();

            return $this->toOutput($entity);
        }

        $result = $this->apiClient->verifyCredentials(
            $entity->getDeliveryCompany(),
            $login,
            $password,
        );

        if ($result['success']) {
            $entity->markAsVerified();
        } else {
            $entity->markAsUnverified($result['message'] ?? 'Échec vérification');
        }

        $this->em->flush();

        return $this->toOutput($entity);
    }

    private function getBoutique(string $id): Boutique
    {
        $entity = $this->boutiqueRepository->find($id);
        if (!$entity) {
            throw new NotFoundHttpException('Boutique not found');
        }

        return $entity;
    }

    private function findAccount(Boutique $boutique, string $id): BoutiqueDeliveryAccount
    {
        $entity = $this->repository->find($id);
        if (!$entity || $entity->getBoutique()->getId() !== $boutique->getId()) {
            throw new NotFoundHttpException('Delivery account not found');
        }

        return $entity;
    }

    private function toOutput(BoutiqueDeliveryAccount $entity): BoutiqueDeliveryAccountOutput
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
