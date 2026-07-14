<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add independent boutique publication status';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE boutique ADD is_published BOOLEAN NOT NULL DEFAULT FALSE');
        $this->addSql('UPDATE boutique SET is_published = TRUE WHERE status = \'active\'');
        $this->addSql('ALTER TABLE boutique ALTER is_published DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE boutique DROP is_published');
    }
}
