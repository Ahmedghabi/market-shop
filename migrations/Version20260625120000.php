<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260625120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add delivery company, boutique delivery account, and order delivery fields.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE delivery_company (
            name VARCHAR(120) NOT NULL,
            slug VARCHAR(120) NOT NULL,
            base_url VARCHAR(255) NOT NULL,
            auth_endpoint VARCHAR(255) DEFAULT NULL,
            submit_order_endpoint VARCHAR(255) NOT NULL,
            track_endpoint VARCHAR(255) DEFAULT NULL,
            description TEXT DEFAULT NULL,
            is_active BOOLEAN NOT NULL,
            id UUID NOT NULL,
            PRIMARY KEY (id)
        )');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DELIVERY_COMPANY_SLUG ON delivery_company (slug)');

        $this->addSql('CREATE TABLE boutique_delivery_account (
            encrypted_login VARCHAR(512) NOT NULL,
            encrypted_password VARCHAR(512) NOT NULL,
            is_verified BOOLEAN NOT NULL,
            verified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            last_error VARCHAR(255) DEFAULT NULL,
            is_active BOOLEAN NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            id UUID NOT NULL,
            boutique_id UUID NOT NULL,
            delivery_company_id UUID NOT NULL,
            PRIMARY KEY (id)
        )');
        $this->addSql('CREATE INDEX IDX_BD_BOUTIQUE ON boutique_delivery_account (boutique_id)');
        $this->addSql('CREATE INDEX IDX_BD_COMPANY ON boutique_delivery_account (delivery_company_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_BOUTIQUE_DELIVERY ON boutique_delivery_account (boutique_id, delivery_company_id)');
        $this->addSql('ALTER TABLE boutique_delivery_account ADD CONSTRAINT FK_BD_BOUTIQUE FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE boutique_delivery_account ADD CONSTRAINT FK_BD_COMPANY FOREIGN KEY (delivery_company_id) REFERENCES delivery_company (id) ON DELETE CASCADE NOT DEFERRABLE');

        $this->addSql('ALTER TABLE customer_order ADD submitted_to_delivery BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('ALTER TABLE customer_order ADD submitted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD delivery_error TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD delivery_retry_count INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE customer_order ADD last_retry_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD delivery_account_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD CONSTRAINT FK_ORDER_DELIVERY_ACCOUNT FOREIGN KEY (delivery_account_id) REFERENCES boutique_delivery_account (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_ORDER_DELIVERY_SUBMIT ON customer_order (submitted_to_delivery, delivery_retry_count)');
        $this->addSql('COMMENT ON COLUMN customer_order.submitted_to_delivery IS \'(DC2Type:boolean)\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE customer_order DROP CONSTRAINT FK_ORDER_DELIVERY_ACCOUNT');
        $this->addSql('DROP INDEX IDX_ORDER_DELIVERY_SUBMIT');
        $this->addSql('ALTER TABLE customer_order DROP submitted_to_delivery');
        $this->addSql('ALTER TABLE customer_order DROP submitted_at');
        $this->addSql('ALTER TABLE customer_order DROP delivery_error');
        $this->addSql('ALTER TABLE customer_order DROP delivery_retry_count');
        $this->addSql('ALTER TABLE customer_order DROP last_retry_at');
        $this->addSql('ALTER TABLE customer_order DROP delivery_account_id');
        $this->addSql('ALTER TABLE boutique_delivery_account DROP CONSTRAINT FK_BD_BOUTIQUE');
        $this->addSql('ALTER TABLE boutique_delivery_account DROP CONSTRAINT FK_BD_COMPANY');
        $this->addSql('DROP TABLE boutique_delivery_account');
        $this->addSql('DROP TABLE delivery_company');
    }
}
