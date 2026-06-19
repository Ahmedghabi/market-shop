<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629091921 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE media (type VARCHAR(32) NOT NULL, original_name VARCHAR(255) NOT NULL, file_name VARCHAR(255) NOT NULL, extension VARCHAR(16) NOT NULL, mime_type VARCHAR(64) NOT NULL, size INT NOT NULL, width INT DEFAULT NULL, height INT DEFAULT NULL, duration DOUBLE PRECISION DEFAULT NULL, path VARCHAR(255) NOT NULL, thumbnail_path VARCHAR(255) DEFAULT NULL, compressed_path VARCHAR(255) DEFAULT NULL, alt_text VARCHAR(255) DEFAULT NULL, is_public BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_6A2CA10CAB677BE6 ON media (boutique_id)');
        $this->addSql('CREATE INDEX idx_media_boutique_type ON media (boutique_id, type)');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10CAB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_6A2CA10CAB677BE6');
        $this->addSql('DROP TABLE media');
    }
}
