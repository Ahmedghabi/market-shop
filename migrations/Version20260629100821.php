<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629100821 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE payment_method (name VARCHAR(120) NOT NULL, code VARCHAR(80) NOT NULL, description TEXT DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7B61A1F677153098 ON payment_method (code)');
        $this->addSql('CREATE TABLE shop_payment_method (is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, payment_method_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_EBC0A0F0AB677BE6 ON shop_payment_method (boutique_id)');
        $this->addSql('CREATE INDEX IDX_EBC0A0F05AA1164F ON shop_payment_method (payment_method_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_shop_payment_method ON shop_payment_method (boutique_id, payment_method_id)');
        $this->addSql('ALTER TABLE shop_payment_method ADD CONSTRAINT FK_EBC0A0F0AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shop_payment_method ADD CONSTRAINT FK_EBC0A0F05AA1164F FOREIGN KEY (payment_method_id) REFERENCES payment_method (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql("ALTER TABLE customer_order ADD payment_status VARCHAR(32) NOT NULL DEFAULT 'pending'");
        $this->addSql('ALTER TABLE customer_order ALTER payment_status DROP DEFAULT');
        $this->addSql('ALTER TABLE customer_order ADD payment_method_code VARCHAR(80) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE shop_payment_method DROP CONSTRAINT FK_EBC0A0F0AB677BE6');
        $this->addSql('ALTER TABLE shop_payment_method DROP CONSTRAINT FK_EBC0A0F05AA1164F');
        $this->addSql('DROP TABLE payment_method');
        $this->addSql('DROP TABLE shop_payment_method');
        $this->addSql('ALTER TABLE customer_order DROP payment_status');
        $this->addSql('ALTER TABLE customer_order DROP payment_method_code');
    }
}
