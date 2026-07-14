<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716124000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Align view and quota column defaults with Doctrine mapping';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER INDEX idx_6c7a9e2e4584665a RENAME TO IDX_7FD953524584665A');
        $this->addSql('ALTER TABLE product ALTER views_count DROP DEFAULT');
        $this->addSql('ALTER TABLE quota_definition ALTER price_tnd DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product ALTER views_count SET DEFAULT 0');
        $this->addSql('ALTER TABLE quota_definition ALTER price_tnd SET DEFAULT 0');
        $this->addSql('ALTER INDEX IDX_7FD953524584665A RENAME TO idx_6c7a9e2e4584665a');
    }
}
