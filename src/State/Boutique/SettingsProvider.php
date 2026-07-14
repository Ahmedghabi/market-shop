<?php

namespace App\State\Boutique;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use App\Dto\Boutique\BoutiqueSettingsOutput;
use App\Entity\Boutique;
use App\Entity\BoutiqueSettings;
use App\Repository\BoutiqueRepository;
use App\Security\BoutiqueContext;
use App\Service\FrontOfficeCacheService;
use App\Service\SeoService;
use App\State\Common\BoutiqueAwareProviderTrait;
use Doctrine\ORM\EntityManagerInterface;

/** @implements ProviderInterface<BoutiqueSettingsOutput> */
final readonly class SettingsProvider implements ProviderInterface
{
    use BoutiqueAwareProviderTrait;

    public function __construct(
        private BoutiqueRepository $boutiques,
        private BoutiqueContext $context,
        private EntityManagerInterface $em,
        private FrontOfficeCacheService $cache,
        private SeoService $seo,
    ) {
    }

    public function provide(Operation $operation, array $uriVariables = [], array $context = []): ?BoutiqueSettingsOutput
    {
        unset($operation);

        $boutique = $this->resolveBoutique($uriVariables, $context);
        if (!$boutique) {
            return null;
        }

        $settings = $boutique->getSettings();
        if (!$settings) {
            $settings = new BoutiqueSettings($boutique);
            $this->em->persist($settings);
            $this->em->flush();
        }

        return $this->toOutput($settings);
    }

    /** @param array<string, mixed> $uriVariables */
    private function resolveBoutique(array $uriVariables, array $context): ?Boutique
    {
        if (isset($uriVariables['boutiqueId'])) {
            return $this->boutiques->findBySlugOrId((string) $uriVariables['boutiqueId']);
        }

        return $this->resolveBoutiqueFromRequest($context);
    }

    private function toOutput(BoutiqueSettings $settings): BoutiqueSettingsOutput
    {
        $boutiqueId = (string) $settings->getBoutique()->getId();
        $socialLinks = $settings->getSocialLinks();
        $colorPalette = $settings->getColorPalette();

        $output = new BoutiqueSettingsOutput();
        $output->id = (string) $settings->getId();
        $output->boutiqueId = $boutiqueId;
        $output->shopName = $settings->getBoutique()->getName();
        $output->logoUrl = $settings->getLogoUrl();
        $output->primaryColor = $settings->getPrimaryColor();
        $output->secondaryColor = $settings->getSecondaryColor();
        $output->accentColor = $colorPalette['accent'] ?? null;
        $output->backgroundColor = $colorPalette['background'] ?? null;
        $output->textColor = $colorPalette['text'] ?? null;
        $output->domain = $settings->getDomain();
        $output->contactEmail = $settings->getContactEmail();
        $output->contactPhone = $settings->getContactPhone();
        $output->address = $settings->getAddress();
        $output->slogan = $settings->getSlogan();
        $output->favicon = $settings->getFavicon();
        $output->coverImage = $settings->getCoverImage();
        $output->description = $settings->getDescription();
        $output->fontFamily = $settings->getFontFamily();
        $output->fontSize = $settings->getFontSize();
        $output->borderRadius = $settings->getBorderRadius();
        $output->theme = $settings->getTheme();
        $output->checkoutMode = $settings->getCheckoutMode()->value;
        $output->orderMode = $settings->getOrderMode()->value;
        $output->metaPixelId = $settings->getMetaPixelId();
        $output->googleAnalyticsId = $settings->getGoogleAnalyticsId();
        $output->googleTagManagerId = $settings->getGoogleTagManagerId();
        $output->tiktokPixelId = $settings->getTiktokPixelId();
        $output->maintenanceMode = $settings->isMaintenanceMode();
        $output->maintenanceMessage = $settings->getMaintenanceMessage();
        $output->enableEmailVerification = $settings->isEnableEmailVerification();
        $output->enableCustomerEmailVerification = $settings->isEnableCustomerEmailVerification();
        $output->createAccountAfterOrder = $settings->isCreateAccountAfterOrder();
        $output->facebookUrl = $socialLinks['facebook'] ?? null;
        $output->instagramUrl = $socialLinks['instagram'] ?? null;
        $output->tiktokUrl = $socialLinks['tiktok'] ?? null;
        $output->youtubeUrl = $socialLinks['youtube'] ?? null;
        $output->linkedinUrl = $socialLinks['linkedin'] ?? null;
        $output->xTwitterUrl = $socialLinks['x_twitter'] ?? null;
        $output->whatsappNumber = $socialLinks['whatsapp'] ?? null;
        $output->socialLinks = $socialLinks;
        $output->colorPalette = $colorPalette;
        $output->iconSet = $settings->getIconSet();
        $output->featuredCategories = $settings->getFeaturedCategories();
        $output->frontOfficePages = $settings->getFrontOfficePages();
        $output->navigationItems = $settings->getNavigationItems();
        $output->guestCheckoutFields = $settings->getGuestCheckoutFields();
        $output->contactDetails = $settings->getContactDetails();
        $output->seoConfig = $this->seo->buildShopSeo($settings->getBoutique());
        $output->homepageSections = $settings->getHomepageSections();
        $output->banners = $settings->getBanners();
        $output->catalogConfig = $settings->getCatalogConfig();
        $output->customerFieldConfig = $settings->getCustomerFieldConfig();
        $output->notificationConfig = $settings->getNotificationConfig();
        $output->moduleConfig = $settings->getModuleConfig();
        $output->paymentConfig = $settings->getPaymentConfig();
        $output->shippingConfig = $settings->getShippingConfig();
        $output->languageConfig = $settings->getLanguageConfig();
        $output->headerConfig = $settings->getHeaderConfig();
        $output->footerConfig = $settings->getFooterConfig();
        $output->createdAt = $settings->getBoutique()->getCreatedAt();
        $output->updatedAt = $settings->getBoutique()->getUpdatedAt();

        return $output;
    }
}
