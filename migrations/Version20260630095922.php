<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260630095922 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD country_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD governorate_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD locality_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD shipping_country_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD shipping_governorate_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD shipping_locality_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_country_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_governorate_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_locality_id UUID DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP country_id');
        $this->addSql('ALTER TABLE customer DROP governorate_id');
        $this->addSql('ALTER TABLE customer DROP locality_id');
        $this->addSql('ALTER TABLE customer_order DROP shipping_country_id');
        $this->addSql('ALTER TABLE customer_order DROP shipping_governorate_id');
        $this->addSql('ALTER TABLE customer_order DROP shipping_locality_id');
        $this->addSql('ALTER TABLE invoice DROP customer_country_id');
        $this->addSql('ALTER TABLE invoice DROP customer_governorate_id');
        $this->addSql('ALTER TABLE invoice DROP customer_locality_id');
    }
}
