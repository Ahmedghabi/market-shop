<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260711220621 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add quota/extension system (QuotaDefinition, PlanQuota, Extension, BoutiqueExtension, ExtensionRequest) and SubscriptionPlan currency/displayOrder/themes.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE boutique_extension (
              activated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              activated_by VARCHAR(180) DEFAULT NULL,
              is_active BOOLEAN NOT NULL,
              expiry_notified_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              id UUID NOT NULL,
              boutique_id UUID NOT NULL,
              extension_id UUID NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_F05E346FAB677BE6 ON boutique_extension (boutique_id)');
        $this->addSql('CREATE INDEX IDX_F05E346F812D5EB ON boutique_extension (extension_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE extension (
              code VARCHAR(80) NOT NULL,
              name VARCHAR(160) NOT NULL,
              description TEXT DEFAULT NULL,
              type VARCHAR(32) NOT NULL,
              target_code VARCHAR(80) DEFAULT NULL,
              value INT DEFAULT NULL,
              price_tnd INT NOT NULL,
              duration_months INT DEFAULT NULL,
              requires_validation BOOLEAN NOT NULL,
              is_active BOOLEAN NOT NULL,
              icon VARCHAR(500) DEFAULT NULL,
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              id UUID NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9FB73D7777153098 ON extension (code)');
        $this->addSql(<<<'SQL'
            CREATE TABLE extension_request (
              price_tnd INT NOT NULL,
              status VARCHAR(32) NOT NULL,
              comment TEXT DEFAULT NULL,
              admin_comment TEXT DEFAULT NULL,
              requested_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              paid_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              decided_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              decided_by VARCHAR(180) DEFAULT NULL,
              id UUID NOT NULL,
              boutique_id UUID NOT NULL,
              extension_id UUID NOT NULL,
              invoice_id UUID DEFAULT NULL,
              grant_id UUID DEFAULT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_7467A1F5AB677BE6 ON extension_request (boutique_id)');
        $this->addSql('CREATE INDEX IDX_7467A1F5812D5EB ON extension_request (extension_id)');
        $this->addSql('CREATE INDEX IDX_7467A1F52989F1FD ON extension_request (invoice_id)');
        $this->addSql('CREATE INDEX IDX_7467A1F55C0C89F3 ON extension_request (grant_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE plan_quota (
              limit_value INT DEFAULT NULL,
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              id UUID NOT NULL,
              plan_id UUID NOT NULL,
              quota_id UUID NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_3FBA4A11E899029B ON plan_quota (plan_id)');
        $this->addSql('CREATE INDEX IDX_3FBA4A1154E2C62F ON plan_quota (quota_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_plan_quota ON plan_quota (plan_id, quota_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE quota_definition (
              code VARCHAR(64) NOT NULL,
              name VARCHAR(160) NOT NULL,
              description TEXT DEFAULT NULL,
              unit VARCHAR(32) DEFAULT NULL,
              category VARCHAR(64) DEFAULT NULL,
              icon VARCHAR(64) DEFAULT NULL,
              is_active BOOLEAN NOT NULL,
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              id UUID NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX UNIQ_DB616E1C77153098 ON quota_definition (code)');
        $this->addSql(<<<'SQL'
            CREATE TABLE subscription_plan_theme (
              subscription_plan_id UUID NOT NULL,
              theme_id UUID NOT NULL,
              PRIMARY KEY (subscription_plan_id, theme_id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_636E2919B8CE200 ON subscription_plan_theme (subscription_plan_id)');
        $this->addSql('CREATE INDEX IDX_636E29159027487 ON subscription_plan_theme (theme_id)');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              boutique_extension
            ADD
              CONSTRAINT FK_F05E346FAB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              boutique_extension
            ADD
              CONSTRAINT FK_F05E346F812D5EB FOREIGN KEY (extension_id) REFERENCES extension (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              extension_request
            ADD
              CONSTRAINT FK_7467A1F5AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              extension_request
            ADD
              CONSTRAINT FK_7467A1F5812D5EB FOREIGN KEY (extension_id) REFERENCES extension (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              extension_request
            ADD
              CONSTRAINT FK_7467A1F52989F1FD FOREIGN KEY (invoice_id) REFERENCES invoice (id) ON DELETE
            SET
              NULL NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              extension_request
            ADD
              CONSTRAINT FK_7467A1F55C0C89F3 FOREIGN KEY (grant_id) REFERENCES boutique_extension (id) ON DELETE
            SET
              NULL NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              plan_quota
            ADD
              CONSTRAINT FK_3FBA4A11E899029B FOREIGN KEY (plan_id) REFERENCES subscription_plan (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              plan_quota
            ADD
              CONSTRAINT FK_3FBA4A1154E2C62F FOREIGN KEY (quota_id) REFERENCES quota_definition (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              subscription_plan_theme
            ADD
              CONSTRAINT FK_636E2919B8CE200 FOREIGN KEY (subscription_plan_id) REFERENCES subscription_plan (id) ON DELETE CASCADE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              subscription_plan_theme
            ADD
              CONSTRAINT FK_636E29159027487 FOREIGN KEY (theme_id) REFERENCES theme (id) ON DELETE CASCADE
        SQL);
        $this->addSql("ALTER TABLE subscription_plan ADD currency VARCHAR(8) NOT NULL DEFAULT 'TND'");
        $this->addSql('ALTER TABLE subscription_plan ADD display_order INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE boutique_extension DROP CONSTRAINT FK_F05E346FAB677BE6');
        $this->addSql('ALTER TABLE boutique_extension DROP CONSTRAINT FK_F05E346F812D5EB');
        $this->addSql('ALTER TABLE extension_request DROP CONSTRAINT FK_7467A1F5AB677BE6');
        $this->addSql('ALTER TABLE extension_request DROP CONSTRAINT FK_7467A1F5812D5EB');
        $this->addSql('ALTER TABLE extension_request DROP CONSTRAINT FK_7467A1F52989F1FD');
        $this->addSql('ALTER TABLE extension_request DROP CONSTRAINT FK_7467A1F55C0C89F3');
        $this->addSql('ALTER TABLE plan_quota DROP CONSTRAINT FK_3FBA4A11E899029B');
        $this->addSql('ALTER TABLE plan_quota DROP CONSTRAINT FK_3FBA4A1154E2C62F');
        $this->addSql('ALTER TABLE subscription_plan_theme DROP CONSTRAINT FK_636E2919B8CE200');
        $this->addSql('ALTER TABLE subscription_plan_theme DROP CONSTRAINT FK_636E29159027487');
        $this->addSql('DROP TABLE boutique_extension');
        $this->addSql('DROP TABLE extension');
        $this->addSql('DROP TABLE extension_request');
        $this->addSql('DROP TABLE plan_quota');
        $this->addSql('DROP TABLE quota_definition');
        $this->addSql('DROP TABLE subscription_plan_theme');
        $this->addSql('ALTER TABLE subscription_plan DROP currency');
        $this->addSql('ALTER TABLE subscription_plan DROP display_order');
    }
}
