<?php

namespace App\State\Payment;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Payment\ShopPaymentMethodInput;
use App\Dto\Payment\ShopPaymentMethodOutput;
use App\Entity\ShopPaymentMethod;
use App\Repository\BoutiqueRepository;
use App\Repository\PaymentMethodRepository;
use App\Repository\ShopPaymentMethodRepository;
use App\Security\BoutiqueContext;
use App\Service\Delivery\EncryptionService;
use App\Service\FrontOfficeCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<ShopPaymentMethodOutput|null> */
final readonly class ShopPaymentMethodProcessor implements ProcessorInterface
{
    public function __construct(
        private BoutiqueRepository $boutiques,
        private ShopPaymentMethodRepository $shopMethods,
        private PaymentMethodRepository $methods,
        private EntityManagerInterface $em,
        private BoutiqueContext $context,
        private EncryptionService $encryption,
        private FrontOfficeCacheService $cache,
        private ShopPaymentMethodProvider $provider,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): ?ShopPaymentMethodOutput
    {
        unset($context);

        $boutique = $this->boutiques->findBySlugOrId((string) ($uriVariables['boutiqueId'] ?? ''));
        if (!$boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }
        if (!$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        if ($operation instanceof Delete) {
            $shopMethod = $this->findShopMethod($boutique, (string) ($uriVariables['id'] ?? ''));
            $this->em->remove($shopMethod);
            $this->em->flush();
            $this->cache->invalidatePaymentMethods((string) $boutique->getId());

            return null;
        }

        assert($data instanceof ShopPaymentMethodInput);
        $paymentMethod = $this->methods->find($data->paymentMethodId);
        if (!$paymentMethod instanceof \App\Entity\PaymentMethod) {
            throw new NotFoundHttpException('Payment method not found');
        }
        if (!$paymentMethod->isActive() || !$paymentMethod->isVisible()) {
            throw new BadRequestHttpException('Payment method is not available globally');
        }

        if (isset($uriVariables['id'])) {
            $shopMethod = $this->findShopMethod($boutique, (string) $uriVariables['id']);
            $shopMethod->setPaymentMethod($paymentMethod);
            $shopMethod->setIsActive($data->isActive);
        } else {
            $existing = $this->shopMethods->findOneByBoutiqueAndMethod($boutique, $paymentMethod);
            if ($existing instanceof ShopPaymentMethod) {
                throw new BadRequestHttpException('Payment method already configured for this boutique');
            }
            $shopMethod = new ShopPaymentMethod($boutique, $paymentMethod, $data->isActive);
            $this->em->persist($shopMethod);
        }

        $shopMethod->setDisplayOrder($data->displayOrder);
        $shopMethod->setMinimumAmountCents($data->minimumAmountCents);
        $shopMethod->setMaximumAmountCents($data->maximumAmountCents);
        $shopMethod->setIsSandbox($data->isSandbox);
        $shopMethod->setGatewayConfig(array_filter([
            'bankName' => $data->bankName,
            'accountHolder' => $data->accountHolder,
            'iban' => $data->iban,
            'swift' => $data->swift,
            'paymentInstructions' => $data->paymentInstructions,
        ], static fn (mixed $value): bool => null !== $value && '' !== trim((string) $value)));
        $shopMethod->setEncryptedCredentials(
            $this->encryptNullable($data->username, $shopMethod->getEncryptedUsername()),
            $this->encryptNullable($data->password, $shopMethod->getEncryptedPassword()),
            $this->encryptNullable($data->apiKey, $shopMethod->getEncryptedApiKey()),
            $this->encryptNullable($data->secretKey, $shopMethod->getEncryptedSecretKey()),
            $this->encryptNullable($data->webhookSecret, $shopMethod->getEncryptedWebhookSecret()),
        );

        $this->em->flush();
        $this->cache->invalidatePaymentMethods((string) $boutique->getId());

        return $this->provider->provide(new \ApiPlatform\Metadata\Get(), [
            'boutiqueId' => (string) $boutique->getId(),
            'id' => (string) $shopMethod->getId(),
        ]);
    }

    private function findShopMethod(\App\Entity\Boutique $boutique, string $id): ShopPaymentMethod
    {
        $shopMethod = $this->shopMethods->find($id);
        if (!$shopMethod instanceof ShopPaymentMethod || (string) $shopMethod->getBoutique()->getId() !== (string) $boutique->getId()) {
            throw new NotFoundHttpException('Shop payment method not found');
        }

        return $shopMethod;
    }

    private function encryptNullable(?string $value, ?string $existing): ?string
    {
        if (null === $value) {
            return $existing;
        }

        $trimmed = trim($value);

        return '' === $trimmed ? null : $this->encryption->encrypt($trimmed);
    }
}
