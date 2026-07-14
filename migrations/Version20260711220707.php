<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260711220707 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drop transient DB-level defaults on subscription_plan.currency/display_order (defaults are handled at the entity level).';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription_plan ALTER currency DROP DEFAULT');
        $this->addSql('ALTER TABLE subscription_plan ALTER display_order DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE subscription_plan ALTER currency SET DEFAULT \'TND\'');
        $this->addSql('ALTER TABLE subscription_plan ALTER display_order SET DEFAULT 0');
    }
}
