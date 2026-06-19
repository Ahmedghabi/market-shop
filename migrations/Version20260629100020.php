<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629100020 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category ADD og_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD og_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE category ADD og_image VARCHAR(500) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD og_title VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD og_description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE product ADD og_image VARCHAR(500) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE category DROP og_title');
        $this->addSql('ALTER TABLE category DROP og_description');
        $this->addSql('ALTER TABLE category DROP og_image');
        $this->addSql('ALTER TABLE product DROP og_title');
        $this->addSql('ALTER TABLE product DROP og_description');
        $this->addSql('ALTER TABLE product DROP og_image');
    }
}
