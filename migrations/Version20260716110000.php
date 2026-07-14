<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716110000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add a price to quota definitions';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quota_definition ADD price_tnd INT NOT NULL DEFAULT 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE quota_definition DROP price_tnd');
    }
}
