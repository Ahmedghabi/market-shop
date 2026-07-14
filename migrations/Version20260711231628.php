<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260711231628 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add delivery integrations engine: endpoints, shipments, API logs, extended company/credential fields.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE delivery_api_log (
              endpoint_type VARCHAR(32) DEFAULT NULL,
              request_method VARCHAR(8) NOT NULL,
              request_url VARCHAR(500) NOT NULL,
              request_body JSON DEFAULT NULL,
              response_status INT DEFAULT NULL,
              response_body JSON DEFAULT NULL,
              success BOOLEAN NOT NULL,
              error_message TEXT DEFAULT NULL,
              duration_ms INT DEFAULT NULL,
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              id UUID NOT NULL,
              delivery_company_id UUID NOT NULL,
              boutique_id UUID DEFAULT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_delivery_log_company ON delivery_api_log (delivery_company_id)');
        $this->addSql('CREATE INDEX idx_delivery_log_boutique ON delivery_api_log (boutique_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE delivery_endpoint (
              type VARCHAR(32) NOT NULL,
              name VARCHAR(160) NOT NULL,
              url VARCHAR(500) NOT NULL,
              http_method VARCHAR(16) NOT NULL,
              headers JSON NOT NULL,
              response_type VARCHAR(16) NOT NULL,
              is_active BOOLEAN NOT NULL,
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              id UUID NOT NULL,
              company_id UUID NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_A523EE17979B1AD6 ON delivery_endpoint (company_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_delivery_endpoint_type ON delivery_endpoint (company_id, type)');
        $this->addSql(<<<'SQL'
            CREATE TABLE shipment (
              status VARCHAR(32) NOT NULL,
              tracking_number VARCHAR(255) DEFAULT NULL,
              label_url VARCHAR(500) DEFAULT NULL,
              cost_cents INT DEFAULT NULL,
              request_payload JSON DEFAULT NULL,
              response_payload JSON DEFAULT NULL,
              error_message TEXT DEFAULT NULL,
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              id UUID NOT NULL,
              boutique_id UUID NOT NULL,
              order_id UUID NOT NULL,
              delivery_company_id UUID NOT NULL,
              credential_id UUID DEFAULT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_2CB20DC8D9F6D38 ON shipment (order_id)');
        $this->addSql('CREATE INDEX IDX_2CB20DC89DE8DF2 ON shipment (delivery_company_id)');
        $this->addSql('CREATE INDEX IDX_2CB20DC2558A7A5 ON shipment (credential_id)');
        $this->addSql('CREATE INDEX idx_shipment_boutique ON shipment (boutique_id)');
        $this->addSql('CREATE INDEX idx_shipment_status ON shipment (status)');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              delivery_api_log
            ADD
              CONSTRAINT FK_C6A3912F89DE8DF2 FOREIGN KEY (delivery_company_id) REFERENCES delivery_company (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              delivery_api_log
            ADD
              CONSTRAINT FK_C6A3912FAB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE
            SET
              NULL NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              delivery_endpoint
            ADD
              CONSTRAINT FK_A523EE17979B1AD6 FOREIGN KEY (company_id) REFERENCES delivery_company (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              shipment
            ADD
              CONSTRAINT FK_2CB20DCAB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              shipment
            ADD
              CONSTRAINT FK_2CB20DC8D9F6D38 FOREIGN KEY (order_id) REFERENCES customer_order (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              shipment
            ADD
              CONSTRAINT FK_2CB20DC89DE8DF2 FOREIGN KEY (delivery_company_id) REFERENCES delivery_company (id) ON DELETE RESTRICT NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              shipment
            ADD
              CONSTRAINT FK_2CB20DC2558A7A5 FOREIGN KEY (credential_id) REFERENCES boutique_delivery_account (id) ON DELETE
            SET
              NULL NOT DEFERRABLE
        SQL);
        $this->addSql('ALTER TABLE boutique_delivery_account ADD encrypted_api_key VARCHAR(512) DEFAULT NULL');
        $this->addSql('ALTER TABLE boutique_delivery_account ADD encrypted_token VARCHAR(512) DEFAULT NULL');
        $this->addSql('ALTER TABLE boutique_delivery_account ADD encrypted_secret VARCHAR(512) DEFAULT NULL');
        $this->addSql('ALTER TABLE boutique_delivery_account ADD custom_base_url VARCHAR(255) DEFAULT NULL');
        $this->addSql("ALTER TABLE boutique_delivery_account ADD is_default BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE delivery_company ADD provider VARCHAR(64) NOT NULL DEFAULT 'generic_http'");
        $this->addSql("ALTER TABLE delivery_company ADD auth_type VARCHAR(32) NOT NULL DEFAULT 'basic'");
        $this->addSql("ALTER TABLE delivery_company ADD auth_config JSON NOT NULL DEFAULT '{}'");
        $this->addSql("ALTER TABLE delivery_company ADD mapping_config JSON NOT NULL DEFAULT '{}'");
        $this->addSql("ALTER TABLE delivery_company ADD parameters_config JSON NOT NULL DEFAULT '{}'");
        $this->addSql('ALTER TABLE delivery_company ADD logo_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery_company ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT NOW()');
        $this->addSql('ALTER TABLE delivery_company ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery_company DROP auth_endpoint');
        $this->addSql('ALTER TABLE delivery_company DROP submit_order_endpoint');
        $this->addSql('ALTER TABLE delivery_company DROP track_endpoint');
        $this->addSql('ALTER TABLE delivery_company ALTER COLUMN provider DROP DEFAULT');
        $this->addSql('ALTER TABLE delivery_company ALTER COLUMN auth_type DROP DEFAULT');
        $this->addSql('ALTER TABLE delivery_company ALTER COLUMN auth_config DROP DEFAULT');
        $this->addSql('ALTER TABLE delivery_company ALTER COLUMN mapping_config DROP DEFAULT');
        $this->addSql('ALTER TABLE delivery_company ALTER COLUMN parameters_config DROP DEFAULT');
        $this->addSql('ALTER TABLE delivery_company ALTER COLUMN created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE boutique_delivery_account ALTER COLUMN is_default DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_api_log DROP CONSTRAINT FK_C6A3912F89DE8DF2');
        $this->addSql('ALTER TABLE delivery_api_log DROP CONSTRAINT FK_C6A3912FAB677BE6');
        $this->addSql('ALTER TABLE delivery_endpoint DROP CONSTRAINT FK_A523EE17979B1AD6');
        $this->addSql('ALTER TABLE shipment DROP CONSTRAINT FK_2CB20DCAB677BE6');
        $this->addSql('ALTER TABLE shipment DROP CONSTRAINT FK_2CB20DC8D9F6D38');
        $this->addSql('ALTER TABLE shipment DROP CONSTRAINT FK_2CB20DC89DE8DF2');
        $this->addSql('ALTER TABLE shipment DROP CONSTRAINT FK_2CB20DC2558A7A5');
        $this->addSql('DROP TABLE delivery_api_log');
        $this->addSql('DROP TABLE delivery_endpoint');
        $this->addSql('DROP TABLE shipment');
        $this->addSql('ALTER TABLE boutique_delivery_account DROP encrypted_api_key');
        $this->addSql('ALTER TABLE boutique_delivery_account DROP encrypted_token');
        $this->addSql('ALTER TABLE boutique_delivery_account DROP encrypted_secret');
        $this->addSql('ALTER TABLE boutique_delivery_account DROP custom_base_url');
        $this->addSql('ALTER TABLE boutique_delivery_account DROP is_default');
        $this->addSql('ALTER TABLE delivery_company ADD submit_order_endpoint VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE delivery_company ADD track_endpoint VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE delivery_company DROP provider');
        $this->addSql('ALTER TABLE delivery_company DROP auth_type');
        $this->addSql('ALTER TABLE delivery_company DROP auth_config');
        $this->addSql('ALTER TABLE delivery_company DROP mapping_config');
        $this->addSql('ALTER TABLE delivery_company DROP parameters_config');
        $this->addSql('ALTER TABLE delivery_company DROP created_at');
        $this->addSql('ALTER TABLE delivery_company DROP updated_at');
        $this->addSql('ALTER TABLE delivery_company DROP logo_url');
        $this->addSql('ALTER TABLE delivery_company ADD auth_endpoint VARCHAR(255) DEFAULT NULL');
    }
}
