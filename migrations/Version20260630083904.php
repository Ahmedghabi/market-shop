<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630083904 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create chatbot_config table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE chatbot_config (model VARCHAR(64) DEFAULT \'llama3.2:1b\' NOT NULL, system_prompt TEXT DEFAULT NULL, temperature DOUBLE PRECISION DEFAULT 0.7 NOT NULL, max_tokens INT DEFAULT 512 NOT NULL, is_enabled BOOLEAN DEFAULT false NOT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_46FCC9C3AB677BE6 ON chatbot_config (boutique_id)');
        $this->addSql('ALTER TABLE chatbot_config ADD CONSTRAINT FK_46FCC9C3AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE chatbot_config DROP CONSTRAINT FK_46FCC9C3AB677BE6');
        $this->addSql('DROP TABLE chatbot_config');
    }
}
