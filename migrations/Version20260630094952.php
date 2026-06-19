<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260630094952 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer ADD address TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD city VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD postal_code VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD country VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD governorate VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer ADD locality VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD shipping_postal_code VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD shipping_country VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD shipping_governorate VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE customer_order ADD shipping_locality VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_governorate VARCHAR(120) DEFAULT NULL');
        $this->addSql('ALTER TABLE invoice ADD customer_locality VARCHAR(120) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE customer DROP address');
        $this->addSql('ALTER TABLE customer DROP city');
        $this->addSql('ALTER TABLE customer DROP postal_code');
        $this->addSql('ALTER TABLE customer DROP country');
        $this->addSql('ALTER TABLE customer DROP governorate');
        $this->addSql('ALTER TABLE customer DROP locality');
        $this->addSql('ALTER TABLE customer_order DROP shipping_postal_code');
        $this->addSql('ALTER TABLE customer_order DROP shipping_country');
        $this->addSql('ALTER TABLE customer_order DROP shipping_governorate');
        $this->addSql('ALTER TABLE customer_order DROP shipping_locality');
        $this->addSql('ALTER TABLE invoice DROP customer_governorate');
        $this->addSql('ALTER TABLE invoice DROP customer_locality');
    }
}
