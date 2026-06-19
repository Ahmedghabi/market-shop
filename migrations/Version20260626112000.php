<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260626112000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align boutique_settings schema with the BoutiqueSettings entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS theme VARCHAR(80) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS color_palette JSON NOT NULL DEFAULT '{}'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS icon_set JSON NOT NULL DEFAULT '{}'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS featured_categories JSON NOT NULL DEFAULT '[]'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS front_office_pages JSON NOT NULL DEFAULT '[]'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS navigation_items JSON NOT NULL DEFAULT '[]'::json");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS use_delivery_api BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS delivery_api_endpoint VARCHAR(255) DEFAULT NULL");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS meta_pixel_id VARCHAR(50) DEFAULT NULL");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS meta_pixel_id');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS delivery_api_endpoint');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS use_delivery_api');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS navigation_items');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS front_office_pages');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS featured_categories');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS icon_set');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS color_palette');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS theme');
    }
}
