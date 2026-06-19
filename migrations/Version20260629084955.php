<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629084955 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE permission (code VARCHAR(100) NOT NULL, name VARCHAR(160) NOT NULL, module VARCHAR(64) NOT NULL, description TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_permission_code ON permission (code)');
        $this->addSql('CREATE TABLE platform_module (is_enabled BOOLEAN NOT NULL, reason_disabled TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, module_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_platform_module ON platform_module (module_id)');
        $this->addSql('CREATE TABLE shop_module (is_enabled BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, module_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_898600AAAB677BE6 ON shop_module (boutique_id)');
        $this->addSql('CREATE INDEX IDX_898600AAAFC2B591 ON shop_module (module_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_shop_module ON shop_module (boutique_id, module_id)');
        $this->addSql('ALTER TABLE platform_module ADD CONSTRAINT FK_E6C33D75AFC2B591 FOREIGN KEY (module_id) REFERENCES subscription_plan_module (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shop_module ADD CONSTRAINT FK_898600AAAB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE shop_module ADD CONSTRAINT FK_898600AAAFC2B591 FOREIGN KEY (module_id) REFERENCES subscription_plan_module (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE subscription_plan_module ADD icon VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE subscription_plan_module ADD is_core BOOLEAN NOT NULL DEFAULT false');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE platform_module DROP CONSTRAINT FK_E6C33D75AFC2B591');
        $this->addSql('ALTER TABLE shop_module DROP CONSTRAINT FK_898600AAAB677BE6');
        $this->addSql('ALTER TABLE shop_module DROP CONSTRAINT FK_898600AAAFC2B591');
        $this->addSql('DROP TABLE permission');
        $this->addSql('DROP TABLE platform_module');
        $this->addSql('DROP TABLE shop_module');
        $this->addSql('ALTER TABLE subscription_plan_module DROP icon');
        $this->addSql('ALTER TABLE subscription_plan_module DROP is_core');
    }
}
