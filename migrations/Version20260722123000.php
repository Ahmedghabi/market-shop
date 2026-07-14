<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260722123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Remove customer-wide suggestion update permission';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("DELETE FROM role_permission WHERE role_code = 'ROLE_CUSTOMER' AND permission = 'suggestion.update'");
    }

    public function down(Schema $schema): void
    {
        $this->addSql("INSERT INTO role_permission (id, role_code, permission, description) SELECT gen_random_uuid(), 'ROLE_CUSTOMER', 'suggestion.update', NULL WHERE NOT EXISTS (SELECT 1 FROM role_permission WHERE role_code = 'ROLE_CUSTOMER' AND permission = 'suggestion.update')");
    }
}
