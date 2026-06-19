<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260630091525 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE governorate ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE governorate ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN governorate.id IS \'\'');
        $this->addSql('ALTER INDEX uniq_b42d9c0f5e237e06 RENAME TO UNIQ_85F5D6BB5E237E06');
        $this->addSql('ALTER INDEX uniq_b42d9c0f77153098 RENAME TO UNIQ_85F5D6BB77153098');
        $this->addSql('ALTER TABLE locality ALTER created_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('ALTER TABLE locality ALTER updated_at TYPE TIMESTAMP(0) WITHOUT TIME ZONE');
        $this->addSql('COMMENT ON COLUMN locality.id IS \'\'');
        $this->addSql('COMMENT ON COLUMN locality.governorate_id IS \'\'');
        $this->addSql('ALTER INDEX idx_2d04b6c57175dfd2 RENAME TO IDX_E1D6B8E6B5FFB04E');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE governorate ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE governorate ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN governorate.id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER INDEX uniq_85f5d6bb5e237e06 RENAME TO uniq_b42d9c0f5e237e06');
        $this->addSql('ALTER INDEX uniq_85f5d6bb77153098 RENAME TO uniq_b42d9c0f77153098');
        $this->addSql('ALTER TABLE locality ALTER created_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('ALTER TABLE locality ALTER updated_at TYPE TIMESTAMP(0) WITH TIME ZONE');
        $this->addSql('COMMENT ON COLUMN locality.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN locality.governorate_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER INDEX idx_e1d6b8e6b5ffb04e RENAME TO idx_2d04b6c57175dfd2');
    }
}
