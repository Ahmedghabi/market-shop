<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260623000003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add guest checkout contact and shipping snapshot fields to orders';
    }

    public function up(Schema $schema): void
    {
        unset($schema);

        $this->addSql('ALTER TABLE customer_order ADD customer_name VARCHAR(240) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD customer_email VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD customer_phone VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD shipping_address TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD shipping_city VARCHAR(120) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        unset($schema);

        $this->addSql('ALTER TABLE customer_order DROP customer_name');
        $this->addSql('ALTER TABLE customer_order DROP customer_email');
        $this->addSql('ALTER TABLE customer_order DROP customer_phone');
        $this->addSql('ALTER TABLE customer_order DROP shipping_address');
        $this->addSql('ALTER TABLE customer_order DROP shipping_city');
    }
}
