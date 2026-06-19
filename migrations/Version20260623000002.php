<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260623000002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create cart and cart item tables for boutique-scoped cookie carts';
    }

    public function up(Schema $schema): void
    {
        unset($schema);

        $this->addSql('CREATE TABLE cart (
            id UUID NOT NULL,
            boutique_id UUID NOT NULL,
            customer_id UUID DEFAULT NULL,
            session_token VARCHAR(80) NOT NULL,
            status VARCHAR(32) NOT NULL,
            currency VARCHAR(3) NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX idx_cart_boutique_status ON cart (boutique_id, status)');
        $this->addSql('CREATE INDEX IDX_BA388B7A76ED395 ON cart (boutique_id)');
        $this->addSql('CREATE INDEX IDX_BA388B79395C3F3 ON cart (customer_id)');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B7A76ED395 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cart ADD CONSTRAINT FK_BA388B79395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE cart_item (
            id UUID NOT NULL,
            cart_id UUID NOT NULL,
            product_id UUID DEFAULT NULL,
            quantity INT NOT NULL,
            unit_price_cents INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX IDX_F0FE25271AD5CDBF ON cart_item (cart_id)');
        $this->addSql('CREATE INDEX IDX_F0FE25274584665A ON cart_item (product_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_cart_item_product ON cart_item (cart_id, product_id)');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25271AD5CDBF FOREIGN KEY (cart_id) REFERENCES cart (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        unset($schema);

        $this->addSql('ALTER TABLE cart_item DROP CONSTRAINT FK_F0FE25271AD5CDBF');
        $this->addSql('ALTER TABLE cart_item DROP CONSTRAINT FK_F0FE25274584665A');
        $this->addSql('ALTER TABLE cart DROP CONSTRAINT FK_BA388B7A76ED395');
        $this->addSql('ALTER TABLE cart DROP CONSTRAINT FK_BA388B79395C3F3');
        $this->addSql('DROP TABLE cart_item');
        $this->addSql('DROP TABLE cart');
    }
}
