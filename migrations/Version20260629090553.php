<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629090553 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD image VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD banner VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD is_active BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE category ADD is_featured BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE category ADD show_in_header BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE category ADD show_on_homepage BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE category ADD homepage_display_type VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD homepage_position INT NOT NULL');
        $this->addSql('ALTER TABLE category ADD menu_position INT NOT NULL');
        $this->addSql('ALTER TABLE category ADD show_category_page BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE category ADD products_limit INT NOT NULL');
        $this->addSql('ALTER TABLE category ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD meta_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD meta_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE category ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP description');
        $this->addSql('ALTER TABLE category DROP image');
        $this->addSql('ALTER TABLE category DROP banner');
        $this->addSql('ALTER TABLE category DROP is_active');
        $this->addSql('ALTER TABLE category DROP is_featured');
        $this->addSql('ALTER TABLE category DROP show_in_header');
        $this->addSql('ALTER TABLE category DROP show_on_homepage');
        $this->addSql('ALTER TABLE category DROP homepage_display_type');
        $this->addSql('ALTER TABLE category DROP homepage_position');
        $this->addSql('ALTER TABLE category DROP menu_position');
        $this->addSql('ALTER TABLE category DROP show_category_page');
        $this->addSql('ALTER TABLE category DROP products_limit');
        $this->addSql('ALTER TABLE category DROP meta_title');
        $this->addSql('ALTER TABLE category DROP meta_description');
        $this->addSql('ALTER TABLE category DROP meta_keywords');
        $this->addSql('ALTER TABLE category DROP created_at');
        $this->addSql('ALTER TABLE category DROP updated_at');
    }
}
