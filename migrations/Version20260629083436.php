<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260629083436 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add boutique fields and expanded settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS description TEXT DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS cover_image VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS email VARCHAR(180) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS phone VARCHAR(64) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS website VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS custom_domain VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS is_verified BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS is_featured BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS approved_by VARCHAR(180) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS rejection_reason TEXT DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS owner_id UUID DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique ADD COLUMN IF NOT EXISTS current_subscription_id UUID DEFAULT NULL");

        $this->addSql("ALTER TABLE boutique DROP CONSTRAINT IF EXISTS FK_A1223C547E3C61F9");
        $this->addSql("ALTER TABLE boutique DROP CONSTRAINT IF EXISTS FK_A1223C54DDE45DDE");
        $this->addSql("ALTER TABLE boutique ADD CONSTRAINT FK_A1223C547E3C61F9 FOREIGN KEY (owner_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE");
        $this->addSql("ALTER TABLE boutique ADD CONSTRAINT FK_A1223C54DDE45DDE FOREIGN KEY (current_subscription_id) REFERENCES subscription (id) ON DELETE SET NULL NOT DEFERRABLE");
        $this->addSql("CREATE INDEX IF NOT EXISTS IDX_A1223C547E3C61F9 ON boutique (owner_id)");
        $this->addSql("CREATE INDEX IF NOT EXISTS IDX_A1223C54DDE45DDE ON boutique (current_subscription_id)");

        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS slogan VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS order_mode VARCHAR(32) NOT NULL DEFAULT 'ECOMMERCE'");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS favicon VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS google_analytics_id VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS google_tag_manager_id VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS tiktok_pixel_id VARCHAR(50) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS maintenance_mode BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS maintenance_message TEXT DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS contact_details JSON NOT NULL DEFAULT '{}'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS seo_config JSON NOT NULL DEFAULT '{}'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS homepage_sections JSON NOT NULL DEFAULT '[]'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS banners JSON NOT NULL DEFAULT '[]'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS catalog_config JSON NOT NULL DEFAULT '{}'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS customer_field_config JSON NOT NULL DEFAULT '[]'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS notification_config JSON NOT NULL DEFAULT '{}'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS module_config JSON NOT NULL DEFAULT '{}'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS payment_config JSON NOT NULL DEFAULT '{}'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS shipping_config JSON NOT NULL DEFAULT '{}'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS language_config JSON NOT NULL DEFAULT '{}'::json");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE boutique DROP CONSTRAINT IF EXISTS FK_A1223C547E3C61F9');
        $this->addSql('ALTER TABLE boutique DROP CONSTRAINT IF EXISTS FK_A1223C54DDE45DDE');
        $this->addSql('DROP INDEX IF EXISTS IDX_A1223C547E3C61F9');
        $this->addSql('DROP INDEX IF EXISTS IDX_A1223C54DDE45DDE');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS description');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS cover_image');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS email');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS phone');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS website');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS custom_domain');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS is_verified');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS is_featured');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS approved_at');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS approved_by');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS rejection_reason');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS owner_id');
        $this->addSql('ALTER TABLE boutique DROP COLUMN IF EXISTS current_subscription_id');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS slogan');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS order_mode');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS favicon');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS google_analytics_id');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS google_tag_manager_id');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS tiktok_pixel_id');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS maintenance_mode');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS maintenance_message');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS contact_details');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS seo_config');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS homepage_sections');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS banners');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS catalog_config');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS customer_field_config');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS notification_config');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS module_config');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS payment_config');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS shipping_config');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS language_config');
    }
}
