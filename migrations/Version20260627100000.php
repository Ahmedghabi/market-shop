<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260627100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create auth system tables and update existing entities';
    }

    public function up(Schema $schema): void
    {
        // User table updates
        $this->addSql("ALTER TABLE app_user ADD COLUMN IF NOT EXISTS firstname VARCHAR(120) DEFAULT NULL");
        $this->addSql("ALTER TABLE app_user ADD COLUMN IF NOT EXISTS lastname VARCHAR(120) DEFAULT NULL");
        $this->addSql("ALTER TABLE app_user ADD COLUMN IF NOT EXISTS phone VARCHAR(64) DEFAULT NULL");
        $this->addSql("ALTER TABLE app_user ADD COLUMN IF NOT EXISTS status VARCHAR(32) NOT NULL DEFAULT 'pending'");
        $this->addSql("ALTER TABLE app_user ADD COLUMN IF NOT EXISTS last_login_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL");

        // Customer table updates
        $this->addSql("ALTER TABLE customer ADD COLUMN IF NOT EXISTS user_id UUID DEFAULT NULL");
        $this->addSql("ALTER TABLE customer ADD COLUMN IF NOT EXISTS loyalty_points INT NOT NULL DEFAULT 0");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_customer_user ON customer (user_id)");

        // Boutique settings updates
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS checkout_mode VARCHAR(32) NOT NULL DEFAULT 'ACCOUNT_ONLY'");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS enable_email_verification BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS enable_customer_email_verification BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS create_account_after_order BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS enable_loyalty BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS guest_checkout_fields JSON NOT NULL DEFAULT '[\"firstname\",\"lastname\",\"phone\",\"email\",\"address\",\"city\",\"postalCode\"]'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS loyalty_points_per_amount INT NOT NULL DEFAULT 100");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS loyalty_amount_cents INT NOT NULL DEFAULT 100");

        // UserShop table
        $this->addSql("CREATE TABLE IF NOT EXISTS user_shop (
            id UUID NOT NULL,
            user_id UUID NOT NULL,
            boutique_id UUID NOT NULL,
            role VARCHAR(32) NOT NULL,
            status VARCHAR(32) NOT NULL DEFAULT 'pending',
            created_by UUID DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )");
        $this->addSql("CREATE UNIQUE INDEX IF NOT EXISTS uniq_user_shop ON user_shop (user_id, boutique_id)");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_user_shop_user ON user_shop (user_id)");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_user_shop_boutique ON user_shop (boutique_id)");

        // RolePermission table
        $this->addSql("CREATE TABLE IF NOT EXISTS role_permission (
            id UUID NOT NULL,
            role_code VARCHAR(60) NOT NULL,
            permission VARCHAR(100) NOT NULL,
            description VARCHAR(255) DEFAULT NULL,
            PRIMARY KEY(id)
        )");
        $this->addSql("CREATE UNIQUE INDEX IF NOT EXISTS uniq_role_permission ON role_permission (role_code, permission)");

        // SocialProvider table
        $this->addSql("CREATE TABLE IF NOT EXISTS social_provider (
            id UUID NOT NULL,
            code VARCHAR(32) NOT NULL,
            name VARCHAR(100) NOT NULL,
            is_active BOOLEAN NOT NULL DEFAULT FALSE,
            PRIMARY KEY(id)
        )");
        $this->addSql("CREATE UNIQUE INDEX IF NOT EXISTS uniq_social_provider_code ON social_provider (code)");

        // ShopSocialProvider table
        $this->addSql("CREATE TABLE IF NOT EXISTS shop_social_provider (
            id UUID NOT NULL,
            boutique_id UUID NOT NULL,
            social_provider_id UUID NOT NULL,
            is_active BOOLEAN NOT NULL DEFAULT FALSE,
            PRIMARY KEY(id)
        )");
        $this->addSql("CREATE UNIQUE INDEX IF NOT EXISTS uniq_shop_social_provider ON shop_social_provider (boutique_id, social_provider_id)");

        // CustomerAuthProvider table
        $this->addSql("CREATE TABLE IF NOT EXISTS customer_auth_provider (
            id UUID NOT NULL,
            customer_id UUID NOT NULL,
            provider VARCHAR(32) NOT NULL,
            provider_user_id VARCHAR(255) NOT NULL,
            PRIMARY KEY(id)
        )");
        $this->addSql("CREATE UNIQUE INDEX IF NOT EXISTS uniq_customer_provider ON customer_auth_provider (customer_id, provider)");
        $this->addSql("CREATE UNIQUE INDEX IF NOT EXISTS uniq_provider_user ON customer_auth_provider (provider, provider_user_id)");

        // CustomerLoyalty table
        $this->addSql("CREATE TABLE IF NOT EXISTS customer_loyalty (
            id UUID NOT NULL,
            customer_id UUID NOT NULL,
            boutique_id UUID NOT NULL,
            points_balance INT NOT NULL DEFAULT 0,
            total_earned INT NOT NULL DEFAULT 0,
            total_used INT NOT NULL DEFAULT 0,
            PRIMARY KEY(id)
        )");
        $this->addSql("CREATE UNIQUE INDEX IF NOT EXISTS uniq_customer_loyalty ON customer_loyalty (customer_id, boutique_id)");

        // Insert default social providers
        $this->addSql("INSERT INTO social_provider (id, code, name, is_active) VALUES
            (gen_random_uuid(), 'GOOGLE', 'Google', FALSE),
            (gen_random_uuid(), 'FACEBOOK', 'Facebook', FALSE)
            ON CONFLICT (code) DO NOTHING");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE IF EXISTS customer_loyalty');
        $this->addSql('DROP TABLE IF EXISTS customer_auth_provider');
        $this->addSql('DROP TABLE IF EXISTS shop_social_provider');
        $this->addSql('DROP TABLE IF EXISTS social_provider');
        $this->addSql('DROP TABLE IF EXISTS role_permission');
        $this->addSql('DROP TABLE IF EXISTS user_shop');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS loyalty_amount_cents');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS loyalty_points_per_amount');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS guest_checkout_fields');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS enable_loyalty');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS create_account_after_order');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS enable_customer_email_verification');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS enable_email_verification');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS checkout_mode');
        $this->addSql('ALTER TABLE customer DROP COLUMN IF EXISTS loyalty_points');
        $this->addSql('ALTER TABLE customer DROP COLUMN IF EXISTS user_id');
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS last_login_at');
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS status');
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS phone');
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS lastname');
        $this->addSql('ALTER TABLE app_user DROP COLUMN IF EXISTS firstname');
    }
}
