<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629093104 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE menu (name VARCHAR(255) NOT NULL, position VARCHAR(20) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_7D053A93AB677BE6 ON menu (boutique_id)');
        $this->addSql('CREATE TABLE menu_item (title VARCHAR(255) NOT NULL, type VARCHAR(30) NOT NULL, target VARCHAR(500) DEFAULT NULL, position INT NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, menu_id UUID NOT NULL, parent_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_D754D550CCD7E912 ON menu_item (menu_id)');
        $this->addSql('CREATE INDEX IDX_D754D550727ACA70 ON menu_item (parent_id)');
        $this->addSql('CREATE TABLE theme (name VARCHAR(255) NOT NULL, code VARCHAR(80) NOT NULL, preview_image VARCHAR(500) DEFAULT NULL, is_active BOOLEAN NOT NULL, is_default BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9775E70877153098 ON theme (code)');
        $this->addSql('ALTER TABLE menu ADD CONSTRAINT FK_7D053A93AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE menu_item ADD CONSTRAINT FK_D754D550CCD7E912 FOREIGN KEY (menu_id) REFERENCES menu (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE menu_item ADD CONSTRAINT FK_D754D550727ACA70 FOREIGN KEY (parent_id) REFERENCES menu_item (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE announcement ADD title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE announcement ADD border_color VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE announcement ADD icon VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE announcement ADD link_url VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE announcement ADD priority INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE announcement ALTER priority DROP DEFAULT');
        $this->addSql('ALTER TABLE announcement ADD is_dismissible BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE announcement ALTER is_dismissible DROP DEFAULT');
        $this->addSql('ALTER TABLE announcement ADD display_mode VARCHAR(20) NOT NULL DEFAULT \'FIXED\'');
        $this->addSql('ALTER TABLE announcement ALTER display_mode DROP DEFAULT');
        $this->addSql('ALTER TABLE announcement ADD position VARCHAR(30) NOT NULL DEFAULT \'TOP_PAGE\'');
        $this->addSql('ALTER TABLE announcement ALTER position DROP DEFAULT');
        $this->addSql("ALTER TABLE announcement ADD display_pages JSON NOT NULL DEFAULT '[\"all\"]'");
        $this->addSql('ALTER TABLE announcement ALTER display_pages DROP DEFAULT');
        $this->addSql('ALTER TABLE announcement ADD is_global BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE announcement ALTER is_global DROP DEFAULT');
        $this->addSql('ALTER TABLE announcement ALTER boutique_id DROP NOT NULL');
        $this->addSql('ALTER TABLE boutique_settings ADD cover_image VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE boutique_settings ADD description VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE boutique_settings ADD font_family VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE boutique_settings ADD font_size VARCHAR(10) DEFAULT NULL');
        $this->addSql('ALTER TABLE boutique_settings ADD border_radius VARCHAR(10) DEFAULT NULL');
        $this->addSql("ALTER TABLE boutique_settings ADD header_config JSON NOT NULL DEFAULT '{}'");
        $this->addSql('ALTER TABLE boutique_settings ALTER header_config DROP DEFAULT');
        $this->addSql("ALTER TABLE boutique_settings ADD footer_config JSON NOT NULL DEFAULT '{}'");
        $this->addSql('ALTER TABLE boutique_settings ALTER footer_config DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE menu DROP CONSTRAINT FK_7D053A93AB677BE6');
        $this->addSql('ALTER TABLE menu_item DROP CONSTRAINT FK_D754D550CCD7E912');
        $this->addSql('ALTER TABLE menu_item DROP CONSTRAINT FK_D754D550727ACA70');
        $this->addSql('DROP TABLE menu');
        $this->addSql('DROP TABLE menu_item');
        $this->addSql('DROP TABLE theme');
        $this->addSql('ALTER TABLE announcement DROP title');
        $this->addSql('ALTER TABLE announcement DROP border_color');
        $this->addSql('ALTER TABLE announcement DROP icon');
        $this->addSql('ALTER TABLE announcement DROP link_url');
        $this->addSql('ALTER TABLE announcement DROP priority');
        $this->addSql('ALTER TABLE announcement DROP is_dismissible');
        $this->addSql('ALTER TABLE announcement DROP display_mode');
        $this->addSql('ALTER TABLE announcement DROP position');
        $this->addSql('ALTER TABLE announcement DROP display_pages');
        $this->addSql('ALTER TABLE announcement DROP is_global');
        $this->addSql('ALTER TABLE announcement ALTER boutique_id SET NOT NULL');
        $this->addSql('ALTER TABLE boutique_settings DROP cover_image');
        $this->addSql('ALTER TABLE boutique_settings DROP description');
        $this->addSql('ALTER TABLE boutique_settings DROP font_family');
        $this->addSql('ALTER TABLE boutique_settings DROP font_size');
        $this->addSql('ALTER TABLE boutique_settings DROP border_radius');
        $this->addSql('ALTER TABLE boutique_settings DROP header_config');
        $this->addSql('ALTER TABLE boutique_settings DROP footer_config');
    }
}
