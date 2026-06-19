<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260630080403 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE delivery_rule (name VARCHAR(120) NOT NULL, type VARCHAR(32) NOT NULL, price_cents INT NOT NULL, min_weight_kg DOUBLE PRECISION DEFAULT NULL, max_weight_kg DOUBLE PRECISION DEFAULT NULL, min_distance_km DOUBLE PRECISION DEFAULT NULL, max_distance_km DOUBLE PRECISION DEFAULT NULL, min_cart_amount_cents INT DEFAULT NULL, max_cart_amount_cents INT DEFAULT NULL, priority INT NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_97446456AB677BE6 ON delivery_rule (boutique_id)');
        $this->addSql('ALTER TABLE delivery_rule ADD CONSTRAINT FK_97446456AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE delivery_rule DROP CONSTRAINT FK_97446456AB677BE6');
        $this->addSql('DROP TABLE delivery_rule');
    }
}
