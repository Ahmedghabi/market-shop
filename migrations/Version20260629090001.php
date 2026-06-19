<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629090001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE subscription_module (is_allowed BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, plan_id UUID NOT NULL, module_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_C5D23EAFE899029B ON subscription_module (plan_id)');
        $this->addSql('CREATE INDEX IDX_C5D23EAFAFC2B591 ON subscription_module (module_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_subscription_module ON subscription_module (plan_id, module_id)');
        $this->addSql('ALTER TABLE subscription_module ADD CONSTRAINT FK_C5D23EAFE899029B FOREIGN KEY (plan_id) REFERENCES subscription_plan (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE subscription_module ADD CONSTRAINT FK_C5D23EAFAFC2B591 FOREIGN KEY (module_id) REFERENCES subscription_plan_module (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription_module DROP CONSTRAINT FK_C5D23EAFE899029B');
        $this->addSql('ALTER TABLE subscription_module DROP CONSTRAINT FK_C5D23EAFAFC2B591');
        $this->addSql('DROP TABLE subscription_module');
    }
}
