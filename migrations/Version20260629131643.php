<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629131643 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE notification_event (code VARCHAR(80) NOT NULL, name VARCHAR(160) NOT NULL, is_active BOOLEAN NOT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_FD1AEF5E77153098 ON notification_event (code)');
        $this->addSql('CREATE TABLE notification_log (channel VARCHAR(16) NOT NULL, recipient VARCHAR(180) NOT NULL, event_code VARCHAR(80) NOT NULL, status VARCHAR(16) NOT NULL, sent_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, error_message TEXT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, boutique_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_ED15DF2AB677BE6 ON notification_log (boutique_id)');
        $this->addSql('CREATE TABLE notification_provider (code VARCHAR(80) NOT NULL, name VARCHAR(160) NOT NULL, type VARCHAR(16) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C7D44B3977153098 ON notification_provider (code)');
        $this->addSql('CREATE TABLE notification_template (event_code VARCHAR(80) NOT NULL, channel VARCHAR(16) NOT NULL, subject VARCHAR(255) DEFAULT NULL, content TEXT NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, boutique_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_C2702726AB677BE6 ON notification_template (boutique_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_notification_template ON notification_template (boutique_id, event_code, channel)');
        $this->addSql('ALTER TABLE notification_log ADD CONSTRAINT FK_ED15DF2AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE notification_template ADD CONSTRAINT FK_C2702726AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification_log DROP CONSTRAINT FK_ED15DF2AB677BE6');
        $this->addSql('ALTER TABLE notification_template DROP CONSTRAINT FK_C2702726AB677BE6');
        $this->addSql('DROP TABLE notification_event');
        $this->addSql('DROP TABLE notification_log');
        $this->addSql('DROP TABLE notification_provider');
        $this->addSql('DROP TABLE notification_template');
    }
}
