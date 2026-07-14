<?php

namespace App\Command;

use App\Entity\RolePermission;
use App\Repository\RolePermissionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'app:seed:role-permissions',
    description: 'Seed default role→permission mappings for backoffice access.',
)]
final class SeedRolePermissionsCommand extends Command
{
    /** @var array<string, list<string>> */
    private const ROLE_PERMISSIONS = [
        'ROLE_SUPER_ADMIN' => ['*'],
        'ROLE_BOUTIQUE_ADMIN' => [
            'product.create', 'product.read', 'product.update', 'product.delete', 'product.import', 'product.export',
            'product.category.manage', 'product.inventory.manage',
            'view_products', 'edit_products',
            'order.create', 'order.read', 'order.update', 'order.delete', 'order.status.update', 'order.ship',
            'order.refund', 'order.delivery.manage', 'view_orders',
            'customer.read', 'customer.update', 'customer.delete', 'customer.export', 'customer.import',
            'customer.reviews.manage', 'customer.address.manage', 'review.read', 'view_reviews',
            'cms.page.read', 'cms.page.create', 'cms.page.update', 'cms.page.delete',
            'cms.banner.manage', 'cms.menu.manage', 'cms.slider.manage', 'cms.blog.manage',
            'cms_access', 'cms', 'blog',
            'employee.create', 'employee.read', 'employee.update', 'employee.delete', 'employee.permission.manage',
            'marketing.coupon.manage', 'marketing.promotion.manage', 'marketing.newsletter.send',
            'marketing.newsletter.template', 'marketing.abandoned_cart.manage', 'marketing.gift_card.manage',
            'marketing.seo.manage', 'marketing.loyalty.manage', 'promotions', 'coupons',
            'invoice.read', 'invoice.create', 'invoice.export', 'invoice.update', 'invoice.payment.receive',
            'subscription.plan.read', 'subscription.request.create', 'subscription.request.read', 'subscription.module.manage',
            'shop.settings.manage', 'shop.profile.manage', 'shop.appearance.manage', 'shop.shipping.manage',
            'shop.payment.manage', 'shop.modules.manage', 'shop.delivery_account.manage', 'shop.custom_domain.manage',
            'report.sales.read', 'report.orders.read', 'report.customers.read', 'report.products.read',
            'suggestion.read', 'suggestion.create', 'suggestion.update', 'suggestion.delete', 'suggestion.react',
            'suggestion.comment', 'suggestion.moderate', 'suggestion.publish', 'suggestion.export', 'suggestion.category.manage',
        ],
        'ROLE_CAISSIER' => [
            'product.read', 'view_products',
            'order.create', 'order.read', 'order.update', 'order.status.update', 'view_orders',
            'customer.read',
            'review.read', 'view_reviews',
            'cms.page.read', 'cms_access',
            'marketing.promotion.manage', 'marketing.coupon.manage', 'promotions', 'coupons',
            'order.delivery.manage',
            'suggestion.read', 'suggestion.react', 'suggestion.comment',
        ],
        'ROLE_CUSTOMER' => [
            'suggestion.read', 'suggestion.create', 'suggestion.react', 'suggestion.comment',
        ],
    ];

    public function __construct(
        private readonly RolePermissionRepository $repository,
        private readonly EntityManagerInterface $em,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        unset($input);

        $created = 0;
        foreach (self::ROLE_PERMISSIONS as $roleCode => $permissions) {
            foreach ($permissions as $permission) {
                if ($this->repository->findOneBy(['roleCode' => $roleCode, 'permission' => $permission])) {
                    continue;
                }

                $this->em->persist(new RolePermission(
                    roleCode: $roleCode,
                    permission: $permission,
                    description: null,
                ));
                ++$created;
            }
        }

        $this->em->flush();

        $output->writeln(sprintf('Seeded %d role permission(s).', $created));

        return Command::SUCCESS;
    }
}
