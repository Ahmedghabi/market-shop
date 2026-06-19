<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260630091704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE country (name VARCHAR(64) NOT NULL, code VARCHAR(4) NOT NULL, phone_code VARCHAR(3) DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5373C9665E237E06 ON country (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_5373C96677153098 ON country (code)');
        $this->addSql('ALTER TABLE governorate ADD country_id UUID NOT NULL');
        $this->addSql('ALTER TABLE governorate ADD CONSTRAINT FK_85F5D6BBF92F3E70 FOREIGN KEY (country_id) REFERENCES country (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_85F5D6BBF92F3E70 ON governorate (country_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE country');
        $this->addSql('ALTER TABLE governorate DROP CONSTRAINT FK_85F5D6BBF92F3E70');
        $this->addSql('DROP INDEX IDX_85F5D6BBF92F3E70');
        $this->addSql('ALTER TABLE governorate DROP country_id');
    }
}
