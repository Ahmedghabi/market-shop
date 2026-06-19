<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629130952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE invoice (invoice_number VARCHAR(32) NOT NULL, type VARCHAR(32) NOT NULL, status VARCHAR(32) NOT NULL, currency VARCHAR(3) NOT NULL, subtotal INT NOT NULL, discount_total INT NOT NULL, tax_total INT NOT NULL, shipping_total INT NOT NULL, total INT NOT NULL, issued_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, due_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, paid_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, pdf_path VARCHAR(500) DEFAULT NULL, boutique_name VARCHAR(255) DEFAULT NULL, boutique_email VARCHAR(180) DEFAULT NULL, boutique_phone VARCHAR(64) DEFAULT NULL, boutique_address TEXT DEFAULT NULL, customer_name VARCHAR(240) DEFAULT NULL, customer_email VARCHAR(180) DEFAULT NULL, customer_phone VARCHAR(64) DEFAULT NULL, customer_address TEXT DEFAULT NULL, customer_city VARCHAR(120) DEFAULT NULL, customer_postal_code VARCHAR(32) DEFAULT NULL, customer_country VARCHAR(120) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, customer_id UUID DEFAULT NULL, order_id UUID DEFAULT NULL, subscription_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_90651744AB677BE6 ON invoice (boutique_id)');
        $this->addSql('CREATE INDEX IDX_906517449395C3F3 ON invoice (customer_id)');
        $this->addSql('CREATE INDEX IDX_906517448D9F6D38 ON invoice (order_id)');
        $this->addSql('CREATE INDEX IDX_906517449A1887DC ON invoice (subscription_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_invoice_number ON invoice (invoice_number)');
        $this->addSql('CREATE TABLE invoice_item (description VARCHAR(255) NOT NULL, quantity INT NOT NULL, unit_price INT NOT NULL, discount INT NOT NULL, tax INT NOT NULL, total INT NOT NULL, id UUID NOT NULL, invoice_id UUID NOT NULL, product_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1DDE477B2989F1FD ON invoice_item (invoice_id)');
        $this->addSql('CREATE INDEX IDX_1DDE477B4584665A ON invoice_item (product_id)');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_90651744AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517448D9F6D38 FOREIGN KEY (order_id) REFERENCES customer_order (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE invoice ADD CONSTRAINT FK_906517449A1887DC FOREIGN KEY (subscription_id) REFERENCES subscription (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE invoice_item ADD CONSTRAINT FK_1DDE477B2989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE invoice_item ADD CONSTRAINT FK_1DDE477B4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_90651744AB677BE6');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_906517449395C3F3');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_906517448D9F6D38');
        $this->addSql('ALTER TABLE invoice DROP CONSTRAINT FK_906517449A1887DC');
        $this->addSql('ALTER TABLE invoice_item DROP CONSTRAINT FK_1DDE477B2989F1FD');
        $this->addSql('ALTER TABLE invoice_item DROP CONSTRAINT FK_1DDE477B4584665A');
        $this->addSql('DROP TABLE invoice');
        $this->addSql('DROP TABLE invoice_item');
    }
}
