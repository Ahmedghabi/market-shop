<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260625130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add meta_pixel_id column to boutique_settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE boutique_settings ADD meta_pixel_id VARCHAR(50) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE boutique_settings DROP meta_pixel_id');
    }
}
