<?php

namespace App\State\Boutique;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use App\Dto\Boutique\BoutiqueSettingsInput;
use App\Dto\Boutique\BoutiqueSettingsOutput;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use App\Service\FrontOfficeCacheService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/** @implements ProcessorInterface<BoutiqueSettingsOutput> */
final readonly class SettingsProcessor implements ProcessorInterface
{
    public function __construct(
        private BoutiqueRepository $boutiques,
        private EntityManagerInterface $em,
        private BoutiqueContext $context,
        private FrontOfficeCacheService $cache,
        private SettingsProvider $provider,
    ) {
    }

    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): BoutiqueSettingsOutput
    {
        unset($operation, $context);

        $boutiqueId = (string) ($uriVariables['boutiqueId'] ?? $this->context->getBoutiqueId() ?? '');
        $boutique = $this->boutiques->findBySlugOrId($boutiqueId);
        if (!$boutique) {
            throw new NotFoundHttpException('Boutique not found');
        }
        if (!$this->context->canAccessBoutique($boutique)) {
            throw new AccessDeniedHttpException('Access denied');
        }

        $settings = $boutique->getSettings();
        if (!$settings) {
            $settings = new \App\Entity\BoutiqueSettings($boutique);
            $this->em->persist($settings);
        }

        assert($data instanceof BoutiqueSettingsInput);

        if (null !== $data->shopName && '' !== trim($data->shopName)) {
            $boutique->setName(trim($data->shopName));
        }

        $this->applyScalarFields($settings, $data);
        $this->applyJsonFields($settings, $data);

        $this->em->flush();
        $this->cache->invalidateAll((string) $boutique->getId());

        return $this->provider->provide(
            new \ApiPlatform\Metadata\Get(),
            ['boutiqueId' => (string) $boutique->getId()],
        );
    }

    private function applyScalarFields(\App\Entity\BoutiqueSettings $s, BoutiqueSettingsInput $d): void
    {
        $socialLinks = $this->mergeSocialLinks($s->getSocialLinks(), $d);
        $logoUrl = $d->logoUrl;
        $primaryColor = $d->primaryColor;
        $secondaryColor = $d->secondaryColor;
        $domain = $d->domain;
        $contactEmail = $d->contactEmail;
        $contactPhone = $d->contactPhone;
        $address = $d->address;

        if (null !== $logoUrl || null !== $primaryColor || null !== $secondaryColor || null !== $domain || null !== $contactEmail || null !== $contactPhone || null !== $address || $socialLinks !== $s->getSocialLinks()) {
            $s->updateContact(
                $logoUrl ?? $s->getLogoUrl(),
                $primaryColor ?? $s->getPrimaryColor(),
                $secondaryColor ?? $s->getSecondaryColor(),
                $domain ?? $s->getDomain(),
                $contactEmail ?? $s->getContactEmail(),
                $contactPhone ?? $s->getContactPhone(),
                $address ?? $s->getAddress(),
                $socialLinks,
            );
        }
        if (null !== $d->slogan) {
            $s->setSlogan($d->slogan);
        }
        if (null !== $d->favicon) {
            $s->setFavicon($d->favicon);
        }
        if (null !== $d->coverImage) {
            $s->setCoverImage($d->coverImage);
        }
        if (null !== $d->description) {
            $s->setDescription($d->description);
        }
        if (null !== $d->fontFamily) {
            $s->setFontFamily($d->fontFamily);
        }
        if (null !== $d->fontSize) {
            $s->setFontSize($d->fontSize);
        }
        if (null !== $d->borderRadius) {
            $s->setBorderRadius($d->borderRadius);
        }
        if (null !== $d->theme) {
            $s->setTheme($d->theme);
        }
        if (null !== $d->metaPixelId) {
            $s->setMetaPixelId($d->metaPixelId);
        }
        if (null !== $d->googleAnalyticsId) {
            $s->setGoogleAnalyticsId($d->googleAnalyticsId);
        }
        if (null !== $d->googleTagManagerId) {
            $s->setGoogleTagManagerId($d->googleTagManagerId);
        }
        if (null !== $d->tiktokPixelId) {
            $s->setTiktokPixelId($d->tiktokPixelId);
        }
        if (null !== $d->maintenanceMode) {
            $s->setMaintenanceMode($d->maintenanceMode);
        }
        if (null !== $d->maintenanceMessage) {
            $s->setMaintenanceMessage($d->maintenanceMessage);
        }
        if (null !== $d->checkoutMode) {
            $s->setCheckoutMode(\App\Enum\CheckoutMode::tryFrom($d->checkoutMode) ?? $s->getCheckoutMode());
        }
        if (null !== $d->orderMode) {
            $s->setOrderMode(\App\Enum\OrderMode::tryFrom($d->orderMode) ?? $s->getOrderMode());
        }
        if (null !== $d->enableEmailVerification) {
            $s->setEnableEmailVerification($d->enableEmailVerification);
        }
        if (null !== $d->enableCustomerEmailVerification) {
            $s->setEnableCustomerEmailVerification($d->enableCustomerEmailVerification);
        }
        if (null !== $d->createAccountAfterOrder) {
            $s->setCreateAccountAfterOrder($d->createAccountAfterOrder);
        }
        if (null !== $d->enableLoyalty) {
            $s->setEnableLoyalty($d->enableLoyalty);
        }
        if (null !== $d->loyaltyPointsPerAmount) {
            $s->setLoyaltyPointsPerAmount($d->loyaltyPointsPerAmount);
        }
        if (null !== $d->loyaltyAmountCents) {
            $s->setLoyaltyAmountCents($d->loyaltyAmountCents);
        }
    }

    private function applyJsonFields(\App\Entity\BoutiqueSettings $s, BoutiqueSettingsInput $d): void
    {
        $colorPalette = $this->mergeColorPalette($s->getColorPalette(), $d);
        if ($colorPalette !== $s->getColorPalette()) {
            $s->setColorPalette($colorPalette);
        }
        if ([] !== $d->iconSet) {
            $s->setIconSet($d->iconSet);
        }
        if ([] !== $d->featuredCategories) {
            $s->setFeaturedCategories($d->featuredCategories);
        }
        if ([] !== $d->frontOfficePages) {
            $s->setFrontOfficePages($d->frontOfficePages);
        }
        if ([] !== $d->navigationItems) {
            $s->setNavigationItems($d->navigationItems);
        }
        if ([] !== $d->guestCheckoutFields) {
            $s->setGuestCheckoutFields($d->guestCheckoutFields);
        }
        if ([] !== $d->contactDetails) {
            $s->setContactDetails($d->contactDetails);
        }
        if ([] !== $d->seoConfig) {
            $s->setSeoConfig($d->seoConfig);
        }
        if ([] !== $d->homepageSections) {
            $s->setHomepageSections($d->homepageSections);
        }
        if ([] !== $d->banners) {
            $s->setBanners($d->banners);
        }
        if ([] !== $d->catalogConfig) {
            $s->setCatalogConfig($d->catalogConfig);
        }
        if ([] !== $d->customerFieldConfig) {
            $s->setCustomerFieldConfig($d->customerFieldConfig);
        }
        if ([] !== $d->notificationConfig) {
            $s->setNotificationConfig($d->notificationConfig);
        }
        if ([] !== $d->moduleConfig) {
            $s->setModuleConfig($d->moduleConfig);
        }
        if ([] !== $d->paymentConfig) {
            $s->setPaymentConfig($d->paymentConfig);
        }
        if ([] !== $d->shippingConfig) {
            $s->setShippingConfig($d->shippingConfig);
        }
        if ([] !== $d->languageConfig) {
            $s->setLanguageConfig($d->languageConfig);
        }
        if ([] !== $d->headerConfig) {
            $s->setHeaderConfig($d->headerConfig);
        }
        if ([] !== $d->footerConfig) {
            $s->setFooterConfig($d->footerConfig);
        }
    }

    /** @param array<string, string> $current */
    private function mergeSocialLinks(array $current, BoutiqueSettingsInput $data): array
    {
        $socialLinks = [] !== $data->socialLinks ? $data->socialLinks : $current;

        $aliases = [
            'facebook' => $data->facebookUrl,
            'instagram' => $data->instagramUrl,
            'tiktok' => $data->tiktokUrl,
            'youtube' => $data->youtubeUrl,
            'linkedin' => $data->linkedinUrl,
            'x_twitter' => $data->xTwitterUrl,
            'whatsapp' => $data->whatsappNumber,
        ];

        foreach ($aliases as $key => $value) {
            if (null !== $value) {
                $socialLinks[$key] = $value;
            }
        }

        return $socialLinks;
    }

    /** @param array<string, string> $current */
    private function mergeColorPalette(array $current, BoutiqueSettingsInput $data): array
    {
        $colorPalette = [] !== $data->colorPalette ? array_replace($current, $data->colorPalette) : $current;

        $aliases = [
            'accent' => $data->accentColor,
            'background' => $data->backgroundColor,
            'text' => $data->textColor,
        ];

        foreach ($aliases as $key => $value) {
            if (null !== $value) {
                $colorPalette[$key] = $value;
            }
        }

        return $colorPalette;
    }
}
