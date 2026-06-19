<?php

namespace App\State\Payment;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Payment\ShopPaymentMethodOutput;
use App\Entity\ShopPaymentMethod;
use App\Repository\ShopPaymentMethodRepository;
use App\Security\BoutiqueContext;
use App\Service\AppConfigService;
use App\Service\FrontOfficeCacheService;
use App\State\Common\BoutiqueAwareProviderTrait;
use Symfony\Bundle\SecurityBundle\Security;

/** @implements ProviderInterface<ShopPaymentMethodOutput> */
final readonly class ShopPaymentMethodProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private ShopPaymentMethodRepository $methods,
        private BoutiqueContext $context,
        private Security $security,
        private FrontOfficeCacheService $cache,
        private AppConfigService $appConfig,
    ) {
    }

    /** @return list<ShopPaymentMethodOutput>|ShopPaymentMethodOutput|null */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): array|ShopPaymentMethodOutput|null
    {
        unset($operation);

        $boutique = $this->resolveBoutiqueFromRequest($context);
        if (!$boutique) {
            return isset($uriVariables['id']) ? null : [];
        }

        $canManage = null !== $this->security->getUser() && $this->context->canAccessBoutique($boutique);

        if (!$canManage && !$this->appConfig->isModuleEnabled('paiements')) {
            return isset($uriVariables['id']) ? null : [];
        }

        if (isset($uriVariables['id'])) {
            $method = $this->methods->find((string) $uriVariables['id']);

            return $method instanceof ShopPaymentMethod
                && (string) $method->getBoutique()->getId() === (string) $boutique->getId()
                && ($canManage || $this->isStorefrontAvailable($method))
                ? $this->toOutput($method)
                : null;
        }

        $methods = $canManage
            ? $this->methods->findByBoutique($boutique)
            : null;

        if (!$canManage) {
            return array_map([$this, 'fromCachedArray'], $this->cache->getPaymentMethods((string) $boutique->getId()));
        }

        return array_map([$this, 'toOutput'], $methods);
    }

    private function toOutput(ShopPaymentMethod $method): ShopPaymentMethodOutput
    {
        $paymentMethod = $method->getPaymentMethod();
        $output = new ShopPaymentMethodOutput();
        $output->id = (string) $method->getId();
        $output->boutiqueId = (string) $method->getBoutique()->getId();
        $output->paymentMethodId = (string) $paymentMethod->getId();
        $output->name = $paymentMethod->getName();
        $output->code = $paymentMethod->getCode();
        $output->description = $paymentMethod->getDescription();
        $output->logo = $paymentMethod->getLogo();
        $output->type = $paymentMethod->getType()->value;
        $output->isActive = $method->isActive();
        $output->isGloballyActive = $paymentMethod->isActive();
        $output->isVisible = $paymentMethod->isVisible();
        $output->displayOrder = $method->getDisplayOrder();
        $output->minimumAmountCents = $method->getMinimumAmountCents();
        $output->maximumAmountCents = $method->getMaximumAmountCents();
        $output->isSandbox = $method->isSandbox();
        $output->hasUsername = null !== $method->getEncryptedUsername();
        $output->hasPassword = null !== $method->getEncryptedPassword();
        $output->hasApiKey = null !== $method->getEncryptedApiKey();
        $output->hasSecretKey = null !== $method->getEncryptedSecretKey();
        $output->hasWebhookSecret = null !== $method->getEncryptedWebhookSecret();
        $output->gatewayConfig = $method->getGatewayConfig();
        $output->createdAt = $method->getCreatedAt();
        $output->updatedAt = $method->getUpdatedAt();

        return $output;
    }

    private function isStorefrontAvailable(ShopPaymentMethod $method): bool
    {
        return $method->isActive() && $method->getPaymentMethod()->isActive() && $method->getPaymentMethod()->isVisible();
    }

    /** @param array<string, mixed> $method */
    private function fromCachedArray(array $method): ShopPaymentMethodOutput
    {
        $output = new ShopPaymentMethodOutput();
        $output->id = (string) $method['id'];
        $output->boutiqueId = '';
        $output->paymentMethodId = (string) $method['paymentMethodId'];
        $output->name = (string) $method['name'];
        $output->code = (string) $method['code'];
        $output->description = $method['description'] ? (string) $method['description'] : null;
        $output->logo = $method['logo'] ? (string) $method['logo'] : null;
        $output->type = (string) $method['type'];
        $output->isActive = true;
        $output->isGloballyActive = true;
        $output->isVisible = true;
        $output->displayOrder = (int) ($method['displayOrder'] ?? 0);
        $output->minimumAmountCents = isset($method['minimumAmountCents']) ? (int) $method['minimumAmountCents'] : null;
        $output->maximumAmountCents = isset($method['maximumAmountCents']) ? (int) $method['maximumAmountCents'] : null;
        $output->isSandbox = (bool) ($method['isSandbox'] ?? false);
        $output->hasUsername = false;
        $output->hasPassword = false;
        $output->hasApiKey = false;
        $output->hasSecretKey = false;
        $output->hasWebhookSecret = false;
        $output->gatewayConfig = is_array($method['gatewayConfig'] ?? null) ? $method['gatewayConfig'] : [];
        $output->createdAt = new \DateTimeImmutable();
        $output->updatedAt = null;

        return $output;
    }
}
