<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629091543 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE brand (name VARCHAR(160) NOT NULL, slug VARCHAR(180) NOT NULL, logo VARCHAR(255) DEFAULT NULL, description TEXT DEFAULT NULL, website VARCHAR(255) DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, boutique_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1C52F958AB677BE6 ON brand (boutique_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_brand_boutique_slug ON brand (boutique_id, slug)');
        $this->addSql('CREATE TABLE product_category (id UUID NOT NULL, product_id UUID NOT NULL, category_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_CDFC73564584665A ON product_category (product_id)');
        $this->addSql('CREATE INDEX IDX_CDFC735612469DE2 ON product_category (category_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_product_category ON product_category (product_id, category_id)');
        $this->addSql('CREATE TABLE product_media (type VARCHAR(32) NOT NULL, file_path VARCHAR(255) NOT NULL, position INT NOT NULL, alt_text VARCHAR(255) DEFAULT NULL, is_primary BOOLEAN NOT NULL, small_url VARCHAR(255) DEFAULT NULL, large_url VARCHAR(255) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_CB70DA504584665A ON product_media (product_id)');
        $this->addSql('CREATE TABLE product_property (name VARCHAR(80) NOT NULL, value VARCHAR(255) NOT NULL, id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_404276494584665A ON product_property (product_id)');
        $this->addSql('CREATE TABLE product_variant (sku VARCHAR(80) DEFAULT NULL, barcode VARCHAR(80) DEFAULT NULL, selling_price INT NOT NULL, compare_price INT NOT NULL, quantity INT NOT NULL, image VARCHAR(255) DEFAULT NULL, is_default BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_209AA41D4584665A ON product_variant (product_id)');
        $this->addSql('CREATE TABLE product_variant_attribute (attribute_name VARCHAR(80) NOT NULL, attribute_value VARCHAR(120) NOT NULL, id UUID NOT NULL, variant_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_AD0306FB3B69A9AF ON product_variant_attribute (variant_id)');
        $this->addSql('ALTER TABLE brand ADD CONSTRAINT FK_1C52F958AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC73564584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_category ADD CONSTRAINT FK_CDFC735612469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_media ADD CONSTRAINT FK_CB70DA504584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_property ADD CONSTRAINT FK_404276494584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_variant ADD CONSTRAINT FK_209AA41D4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_variant_attribute ADD CONSTRAINT FK_AD0306FB3B69A9AF FOREIGN KEY (variant_id) REFERENCES product_variant (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product ADD barcode VARCHAR(80) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD short_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD status VARCHAR(32) NOT NULL');
        $this->addSql('ALTER TABLE product ADD selling_price INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD compare_price INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD tax_rate INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD weight INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD length INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD width INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD height INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD stock_quantity INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD low_stock_threshold INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD is_featured BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE product ADD is_best_seller BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE product ADD is_new BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE product ADD is_virtual BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE product ADD meta_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD meta_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD meta_keywords VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE product ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD brand_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE product RENAME COLUMN price_cents TO cost_price');
        $this->addSql('ALTER TABLE product RENAME COLUMN active TO manage_stock');
        $this->addSql('ALTER TABLE product ADD CONSTRAINT FK_D34A04AD44F5D008 FOREIGN KEY (brand_id) REFERENCES brand (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_D34A04AD44F5D008 ON product (brand_id)');
        $this->addSql('ALTER TABLE stock_movement ADD variant_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_movement DROP reference');
        $this->addSql('ALTER TABLE stock_movement ALTER reason TYPE TEXT');
        $this->addSql('ALTER TABLE stock_movement ADD CONSTRAINT FK_BB1BC1B53B69A9AF FOREIGN KEY (variant_id) REFERENCES product_variant (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_BB1BC1B53B69A9AF ON stock_movement (variant_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE brand DROP CONSTRAINT FK_1C52F958AB677BE6');
        $this->addSql('ALTER TABLE product_category DROP CONSTRAINT FK_CDFC73564584665A');
        $this->addSql('ALTER TABLE product_category DROP CONSTRAINT FK_CDFC735612469DE2');
        $this->addSql('ALTER TABLE product_media DROP CONSTRAINT FK_CB70DA504584665A');
        $this->addSql('ALTER TABLE product_property DROP CONSTRAINT FK_404276494584665A');
        $this->addSql('ALTER TABLE product_variant DROP CONSTRAINT FK_209AA41D4584665A');
        $this->addSql('ALTER TABLE product_variant_attribute DROP CONSTRAINT FK_AD0306FB3B69A9AF');
        $this->addSql('DROP TABLE brand');
        $this->addSql('DROP TABLE product_category');
        $this->addSql('DROP TABLE product_media');
        $this->addSql('DROP TABLE product_property');
        $this->addSql('DROP TABLE product_variant');
        $this->addSql('DROP TABLE product_variant_attribute');
        $this->addSql('ALTER TABLE product DROP CONSTRAINT FK_D34A04AD44F5D008');
        $this->addSql('DROP INDEX IDX_D34A04AD44F5D008');
        $this->addSql('ALTER TABLE product ADD price_cents INT NOT NULL');
        $this->addSql('ALTER TABLE product ADD active BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE product DROP barcode');
        $this->addSql('ALTER TABLE product DROP short_description');
        $this->addSql('ALTER TABLE product DROP status');
        $this->addSql('ALTER TABLE product DROP cost_price');
        $this->addSql('ALTER TABLE product DROP selling_price');
        $this->addSql('ALTER TABLE product DROP compare_price');
        $this->addSql('ALTER TABLE product DROP tax_rate');
        $this->addSql('ALTER TABLE product DROP weight');
        $this->addSql('ALTER TABLE product DROP length');
        $this->addSql('ALTER TABLE product DROP width');
        $this->addSql('ALTER TABLE product DROP height');
        $this->addSql('ALTER TABLE product DROP manage_stock');
        $this->addSql('ALTER TABLE product DROP stock_quantity');
        $this->addSql('ALTER TABLE product DROP low_stock_threshold');
        $this->addSql('ALTER TABLE product DROP is_featured');
        $this->addSql('ALTER TABLE product DROP is_best_seller');
        $this->addSql('ALTER TABLE product DROP is_new');
        $this->addSql('ALTER TABLE product DROP is_virtual');
        $this->addSql('ALTER TABLE product DROP meta_title');
        $this->addSql('ALTER TABLE product DROP meta_description');
        $this->addSql('ALTER TABLE product DROP meta_keywords');
        $this->addSql('ALTER TABLE product DROP published_at');
        $this->addSql('ALTER TABLE product DROP created_at');
        $this->addSql('ALTER TABLE product DROP updated_at');
        $this->addSql('ALTER TABLE product DROP brand_id');
        $this->addSql('ALTER TABLE stock_movement DROP CONSTRAINT FK_BB1BC1B53B69A9AF');
        $this->addSql('DROP INDEX IDX_BB1BC1B53B69A9AF');
        $this->addSql('ALTER TABLE stock_movement ADD reference VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE stock_movement DROP variant_id');
        $this->addSql('ALTER TABLE stock_movement ALTER reason TYPE VARCHAR(255)');
    }
}
