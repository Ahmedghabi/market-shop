<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629084023 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create subscription_plan, subscription_plan_module, subscription_request tables and add subscription_plan_id to subscription';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subscription_plan (name VARCHAR(160) NOT NULL, description TEXT DEFAULT NULL, duration_months INT NOT NULL, price_tnd INT NOT NULL, is_free BOOLEAN NOT NULL, is_visible BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, modules JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE TABLE subscription_plan_module (code VARCHAR(64) NOT NULL, name VARCHAR(160) NOT NULL, description TEXT DEFAULT NULL, category VARCHAR(64) DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_85664ECD77153098 ON subscription_plan_module (code)');
        $this->addSql('CREATE TABLE subscription_request (status VARCHAR(32) NOT NULL, requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, approved_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, approved_by VARCHAR(180) DEFAULT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, subscription_plan_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_48826F04AB677BE6 ON subscription_request (boutique_id)');
        $this->addSql('CREATE INDEX IDX_48826F049B8CE200 ON subscription_request (subscription_plan_id)');
        $this->addSql('ALTER TABLE subscription_request ADD CONSTRAINT FK_48826F04AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE subscription_request ADD CONSTRAINT FK_48826F049B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE subscription ADD subscription_plan_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription ADD CONSTRAINT FK_A3C664D39B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_A3C664D39B8CE200 ON subscription (subscription_plan_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription_request DROP CONSTRAINT FK_48826F04AB677BE6');
        $this->addSql('ALTER TABLE subscription_request DROP CONSTRAINT FK_48826F049B8CE200');
        $this->addSql('DROP TABLE subscription_plan');
        $this->addSql('DROP TABLE subscription_plan_module');
        $this->addSql('DROP TABLE subscription_request');
        $this->addSql('ALTER TABLE subscription DROP CONSTRAINT FK_A3C664D39B8CE200');
        $this->addSql('DROP INDEX IDX_A3C664D39B8CE200');
        $this->addSql('ALTER TABLE subscription DROP subscription_plan_id');
    }
}
