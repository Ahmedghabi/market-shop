<?php

namespace App\Command;

use App\Entity\Permission;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:seed:permissions', description: 'Seed default permissions for all modules')]
final class SeedPermissionsCommand extends Command
{
    private const PERMISSIONS = [
        // Produits
        ['code' => 'product.create', 'name' => 'Créer un produit', 'module' => 'catalogue'],
        ['code' => 'product.read', 'name' => 'Voir les produits', 'module' => 'catalogue'],
        ['code' => 'product.update', 'name' => 'Modifier un produit', 'module' => 'catalogue'],
        ['code' => 'product.delete', 'name' => 'Supprimer un produit', 'module' => 'catalogue'],
        ['code' => 'product.import', 'name' => 'Importer des produits', 'module' => 'catalogue'],
        ['code' => 'product.export', 'name' => 'Exporter des produits', 'module' => 'catalogue'],
        ['code' => 'product.category.manage', 'name' => 'Gérer les catégories', 'module' => 'catalogue'],
        ['code' => 'product.inventory.manage', 'name' => 'Gérer les stocks', 'module' => 'catalogue'],

        // Commandes
        ['code' => 'order.create', 'name' => 'Créer une commande', 'module' => 'commandes'],
        ['code' => 'order.read', 'name' => 'Voir les commandes', 'module' => 'commandes'],
        ['code' => 'order.update', 'name' => 'Modifier une commande', 'module' => 'commandes'],
        ['code' => 'order.delete', 'name' => 'Supprimer une commande', 'module' => 'commandes'],
        ['code' => 'order.status.update', 'name' => 'Changer le statut', 'module' => 'commandes'],
        ['code' => 'order.ship', 'name' => 'Expédier une commande', 'module' => 'commandes'],
        ['code' => 'order.refund', 'name' => 'Rembourser une commande', 'module' => 'commandes'],
        ['code' => 'order.delivery.manage', 'name' => 'Gérer la livraison', 'module' => 'commandes'],

        // Clients
        ['code' => 'customer.read', 'name' => 'Voir les clients', 'module' => 'clients'],
        ['code' => 'customer.update', 'name' => 'Modifier un client', 'module' => 'clients'],
        ['code' => 'customer.delete', 'name' => 'Supprimer un client', 'module' => 'clients'],
        ['code' => 'customer.export', 'name' => 'Exporter les clients', 'module' => 'clients'],
        ['code' => 'customer.import', 'name' => 'Importer des clients', 'module' => 'clients'],
        ['code' => 'customer.reviews.manage', 'name' => 'Gérer les avis', 'module' => 'clients'],
        ['code' => 'customer.address.manage', 'name' => 'Gérer les adresses', 'module' => 'clients'],

        // CMS
        ['code' => 'cms.page.read', 'name' => 'Voir les pages', 'module' => 'cms'],
        ['code' => 'cms.page.create', 'name' => 'Créer une page', 'module' => 'cms'],
        ['code' => 'cms.page.update', 'name' => 'Modifier une page', 'module' => 'cms'],
        ['code' => 'cms.page.delete', 'name' => 'Supprimer une page', 'module' => 'cms'],
        ['code' => 'cms.banner.manage', 'name' => 'Gérer les bannières', 'module' => 'cms'],
        ['code' => 'cms.menu.manage', 'name' => 'Gérer les menus', 'module' => 'cms'],
        ['code' => 'cms.slider.manage', 'name' => 'Gérer les sliders', 'module' => 'cms'],
        ['code' => 'cms.blog.manage', 'name' => 'Gérer le blog', 'module' => 'cms'],

        // Employés
        ['code' => 'employee.create', 'name' => 'Ajouter un employé', 'module' => 'employés'],
        ['code' => 'employee.read', 'name' => 'Voir les employés', 'module' => 'employés'],
        ['code' => 'employee.update', 'name' => 'Modifier un employé', 'module' => 'employés'],
        ['code' => 'employee.delete', 'name' => 'Supprimer un employé', 'module' => 'employés'],
        ['code' => 'employee.permission.manage', 'name' => 'Gérer les permissions', 'module' => 'employés'],

        // Marketing
        ['code' => 'marketing.coupon.manage', 'name' => 'Gérer les coupons', 'module' => 'marketing'],
        ['code' => 'marketing.promotion.manage', 'name' => 'Gérer les promotions', 'module' => 'marketing'],
        ['code' => 'marketing.newsletter.send', 'name' => 'Envoyer newsletter', 'module' => 'marketing'],
        ['code' => 'marketing.newsletter.template', 'name' => 'Gérer les templates', 'module' => 'marketing'],
        ['code' => 'marketing.abandoned_cart.manage', 'name' => 'Gérer paniers abandonnés', 'module' => 'marketing'],
        ['code' => 'marketing.gift_card.manage', 'name' => 'Gérer cartes cadeaux', 'module' => 'marketing'],
        ['code' => 'marketing.seo.manage', 'name' => 'Gérer le SEO', 'module' => 'marketing'],
        ['code' => 'marketing.loyalty.manage', 'name' => 'Gérer fidélité', 'module' => 'marketing'],

        // Facturation
        ['code' => 'invoice.read', 'name' => 'Voir les factures', 'module' => 'facturation'],
        ['code' => 'invoice.create', 'name' => 'Créer une facture', 'module' => 'facturation'],
        ['code' => 'invoice.export', 'name' => 'Exporter les factures', 'module' => 'facturation'],
        ['code' => 'invoice.update', 'name' => 'Modifier une facture', 'module' => 'facturation'],
        ['code' => 'invoice.payment.receive', 'name' => 'Encaisser un paiement', 'module' => 'facturation'],

        // Abonnements
        ['code' => 'subscription.plan.read', 'name' => 'Voir les abonnements', 'module' => 'abonnements'],
        ['code' => 'subscription.request.create', 'name' => 'Demander un abonnement', 'module' => 'abonnements'],
        ['code' => 'subscription.request.read', 'name' => 'Voir les demandes', 'module' => 'abonnements'],
        ['code' => 'subscription.module.manage', 'name' => 'Gérer les modules', 'module' => 'abonnements'],

        // Configuration boutique
        ['code' => 'shop.settings.manage', 'name' => 'Gérer les paramètres', 'module' => 'boutique'],
        ['code' => 'shop.profile.manage', 'name' => 'Gérer le profil', 'module' => 'boutique'],
        ['code' => 'shop.appearance.manage', 'name' => 'Gérer l\'apparence', 'module' => 'boutique'],
        ['code' => 'shop.shipping.manage', 'name' => 'Gérer la livraison', 'module' => 'boutique'],
        ['code' => 'shop.payment.manage', 'name' => 'Gérer les paiements', 'module' => 'boutique'],
        ['code' => 'shop.modules.manage', 'name' => 'Gérer les modules', 'module' => 'boutique'],
        ['code' => 'shop.delivery_account.manage', 'name' => 'Gérer transporteurs', 'module' => 'boutique'],
        ['code' => 'shop.custom_domain.manage', 'name' => 'Gérer le domaine', 'module' => 'boutique'],

        // Rapports
        ['code' => 'report.sales.read', 'name' => 'Voir les ventes', 'module' => 'rapports'],
        ['code' => 'report.orders.read', 'name' => 'Voir rapports commandes', 'module' => 'rapports'],
        ['code' => 'report.customers.read', 'name' => 'Voir rapports clients', 'module' => 'rapports'],
        ['code' => 'report.products.read', 'name' => 'Voir rapports produits', 'module' => 'rapports'],

        // Suggestions
        ['code' => 'suggestion.read', 'name' => 'Voir les suggestions', 'module' => 'suggestions'],
        ['code' => 'suggestion.create', 'name' => 'Créer une suggestion', 'module' => 'suggestions'],
        ['code' => 'suggestion.update', 'name' => 'Modifier une suggestion', 'module' => 'suggestions'],
        ['code' => 'suggestion.delete', 'name' => 'Supprimer une suggestion', 'module' => 'suggestions'],
        ['code' => 'suggestion.react', 'name' => 'Réagir aux suggestions', 'module' => 'suggestions'],
        ['code' => 'suggestion.comment', 'name' => 'Commenter les suggestions', 'module' => 'suggestions'],
        ['code' => 'suggestion.moderate', 'name' => 'Modérer les suggestions', 'module' => 'suggestions'],
        ['code' => 'suggestion.publish', 'name' => 'Publier les suggestions', 'module' => 'suggestions'],
        ['code' => 'suggestion.export', 'name' => 'Exporter les suggestions', 'module' => 'suggestions'],
        ['code' => 'suggestion.category.manage', 'name' => 'Gérer les catégories de suggestions', 'module' => 'suggestions'],

        // Legacy aliases used by the backoffice frontend
        ['code' => 'view_products', 'name' => 'Voir les produits (legacy)', 'module' => 'catalogue'],
        ['code' => 'edit_products', 'name' => 'Modifier les produits (legacy)', 'module' => 'catalogue'],
        ['code' => 'view_orders', 'name' => 'Voir les commandes (legacy)', 'module' => 'commandes'],
        ['code' => 'view_reviews', 'name' => 'Voir les avis (legacy)', 'module' => 'clients'],
        ['code' => 'review.read', 'name' => 'Lire les avis', 'module' => 'clients'],
        ['code' => 'cms_access', 'name' => 'Accès CMS (legacy)', 'module' => 'cms'],
        ['code' => 'cms', 'name' => 'CMS (legacy)', 'module' => 'cms'],
        ['code' => 'blog', 'name' => 'Blog (legacy)', 'module' => 'cms'],
        ['code' => 'promotions', 'name' => 'Promotions (legacy)', 'module' => 'marketing'],
        ['code' => 'coupons', 'name' => 'Coupons (legacy)', 'module' => 'marketing'],
    ];

    public function __construct(
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $repo = $this->em->getRepository(Permission::class);
        $created = 0;

        foreach (self::PERMISSIONS as $data) {
            if ($repo->findOneBy(['code' => $data['code']])) {
                continue;
            }

            $this->em->persist(new Permission(
                code: $data['code'],
                name: $data['name'],
                module: $data['module'],
                description: null,
            ));
            ++$created;
        }

        $this->em->flush();

        $output->writeln(sprintf('Seeded %d permission(s) (%d total defined).', $created, count(self::PERMISSIONS)));

        return Command::SUCCESS;
    }
}
