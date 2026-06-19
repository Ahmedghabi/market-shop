<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629132312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_session (token_id VARCHAR(64) NOT NULL, device_name VARCHAR(255) DEFAULT NULL, device_type VARCHAR(16) NOT NULL, browser VARCHAR(120) DEFAULT NULL, operating_system VARCHAR(120) DEFAULT NULL, ip_address VARCHAR(64) DEFAULT NULL, country VARCHAR(120) DEFAULT NULL, city VARCHAR(120) DEFAULT NULL, last_activity_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, is_current BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, id UUID NOT NULL, user_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_user_session_user ON user_session (user_id)');
        $this->addSql('CREATE INDEX idx_user_session_expires ON user_session (expires_at)');
        $this->addSql('CREATE UNIQUE INDEX uniq_user_session_token ON user_session (token_id)');
        $this->addSql('ALTER TABLE user_session ADD CONSTRAINT FK_8849CBDEA76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('DROP INDEX uniq_provider_user');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_session DROP CONSTRAINT FK_8849CBDEA76ED395');
        $this->addSql('DROP TABLE user_session');
        $this->addSql('CREATE UNIQUE INDEX uniq_provider_user ON customer_auth_provider (provider, provider_user_id)');
    }
}
