<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260625140000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create review table for boutique and product reviews';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE review (
            id UUID NOT NULL,
            boutique_id UUID DEFAULT NULL,
            product_id UUID DEFAULT NULL,
            author_name VARCHAR(120) NOT NULL,
            author_email VARCHAR(180) DEFAULT NULL,
            rating SMALLINT NOT NULL,
            comment TEXT DEFAULT NULL,
            status VARCHAR(16) NOT NULL DEFAULT \'pending\',
            created_at TIMESTAMP(0) WITH TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )');
        $this->addSql('CREATE INDEX idx_review_boutique ON review (boutique_id)');
        $this->addSql('CREATE INDEX idx_review_product ON review (product_id)');
        $this->addSql('CREATE INDEX idx_review_status ON review (status)');
        $this->addSql('COMMENT ON COLUMN review.id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review.boutique_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review.product_id IS \'(DC2Type:uuid)\'');
        $this->addSql('COMMENT ON COLUMN review.created_at IS \'(DC2Type:datetime_immutable_utc)\'');
        $this->addSql('COMMENT ON COLUMN review.updated_at IS \'(DC2Type:datetime_immutable_utc)\'');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_REVIEW_BOUTIQUE FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_REVIEW_PRODUCT FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_REVIEW_BOUTIQUE');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_REVIEW_PRODUCT');
        $this->addSql('DROP TABLE review');
    }
}
