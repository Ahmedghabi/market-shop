<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629103322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE product_favorite (session_id VARCHAR(120) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, user_id UUID DEFAULT NULL, boutique_id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_product_favorite_user ON product_favorite (user_id)');
        $this->addSql('CREATE INDEX idx_product_favorite_session ON product_favorite (session_id)');
        $this->addSql('CREATE INDEX idx_product_favorite_boutique ON product_favorite (boutique_id)');
        $this->addSql('CREATE INDEX idx_product_favorite_product ON product_favorite (product_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_product_favorite_user ON product_favorite (user_id, boutique_id, product_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_product_favorite_session ON product_favorite (session_id, boutique_id, product_id)');
        $this->addSql('CREATE TABLE shop_favorite (session_id VARCHAR(120) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, user_id UUID DEFAULT NULL, boutique_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_shop_favorite_user ON shop_favorite (user_id)');
        $this->addSql('CREATE INDEX idx_shop_favorite_session ON shop_favorite (session_id)');
        $this->addSql('CREATE INDEX idx_shop_favorite_boutique ON shop_favorite (boutique_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_shop_favorite_user ON shop_favorite (user_id, boutique_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_shop_favorite_session ON shop_favorite (session_id, boutique_id)');
        $this->addSql('ALTER TABLE product_favorite ADD CONSTRAINT FK_A375E44EA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_favorite ADD CONSTRAINT FK_A375E44EAB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_favorite ADD CONSTRAINT FK_A375E44E4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shop_favorite ADD CONSTRAINT FK_B37D744FA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shop_favorite ADD CONSTRAINT FK_B37D744FAB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product_favorite DROP CONSTRAINT FK_A375E44EA76ED395');
        $this->addSql('ALTER TABLE product_favorite DROP CONSTRAINT FK_A375E44EAB677BE6');
        $this->addSql('ALTER TABLE product_favorite DROP CONSTRAINT FK_A375E44E4584665A');
        $this->addSql('ALTER TABLE shop_favorite DROP CONSTRAINT FK_B37D744FA76ED395');
        $this->addSql('ALTER TABLE shop_favorite DROP CONSTRAINT FK_B37D744FAB677BE6');
        $this->addSql('DROP TABLE product_favorite');
        $this->addSql('DROP TABLE shop_favorite');
    }
}
