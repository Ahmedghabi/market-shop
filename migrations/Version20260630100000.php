<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260630100000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create governorate and locality tables for Tunisian reference data';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE governorate (id UUID NOT NULL, name VARCHAR(64) NOT NULL, code VARCHAR(5) NOT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B42D9C0F5E237E06 ON governorate (name)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_B42D9C0F77153098 ON governorate (code)');
        $this->addSql('COMMENT ON COLUMN governorate.id IS \'(DC2Type:uuid)\'');
        $this->addSql('CREATE TABLE locality (id UUID NOT NULL, governorate_id UUID NOT NULL, name VARCHAR(120) NOT NULL, postal_code VARCHAR(10) DEFAULT NULL, latitude DOUBLE PRECISION DEFAULT NULL, longitude DOUBLE PRECISION DEFAULT NULL, is_active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_2D04B6C57175DFD2 ON locality (governorate_id)');
        $this->addSql('CREATE INDEX idx_locality_name ON locality (name)');
        $this->addSql('CREATE INDEX idx_locality_postal_code ON locality (postal_code)');
        $this->addSql('COMMENT ON COLUMN locality.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN locality.governorate_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE locality ADD CONSTRAINT FK_2D04B6C57175DFD2 FOREIGN KEY (governorate_id) REFERENCES governorate (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE locality DROP CONSTRAINT FK_2D04B6C57175DFD2');
        $this->addSql('DROP TABLE locality');
        $this->addSql('DROP TABLE governorate');
    }
}
