<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260714163000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add guest access token to chat conversations.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversation ADD guest_access_token VARCHAR(64) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE conversation DROP guest_access_token');
    }
}
