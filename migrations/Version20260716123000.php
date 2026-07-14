<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260716123000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add daily product view aggregation';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_view_daily (id UUID NOT NULL, product_id UUID NOT NULL, view_date DATE NOT NULL, views_count INT NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX uniq_product_view_daily ON product_view_daily (product_id, view_date)');
        $this->addSql('CREATE INDEX idx_product_view_daily_date ON product_view_daily (view_date)');
        $this->addSql('CREATE INDEX IDX_6C7A9E2E4584665A ON product_view_daily (product_id)');
        $this->addSql('ALTER TABLE product_view_daily ADD CONSTRAINT FK_6C7A9E2E4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE product_view_daily');
    }
}
