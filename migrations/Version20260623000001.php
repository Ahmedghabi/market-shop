<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260623000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create subscription table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE subscription (
            id UUID NOT NULL,
            boutique_id UUID NOT NULL,
            plan VARCHAR(32) NOT NULL,
            status VARCHAR(32) NOT NULL,
            start_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            end_date TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            accepted_by VARCHAR(180) DEFAULT NULL,
            accepted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX idx_subscription_boutique ON subscription(boutique_id)');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT fk_subscription_boutique FOREIGN KEY (boutique_id) REFERENCES boutique(id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE subscription');
    }
}
