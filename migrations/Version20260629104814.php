<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629104814 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE announcement ADD subtitle VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE announcement ADD button_color VARCHAR(32) DEFAULT NULL');
        $this->addSql('ALTER TABLE announcement ADD button_text VARCHAR(255) DEFAULT NULL');
        $this->addSql("ALTER TABLE announcement ADD target_category_ids JSON NOT NULL DEFAULT '[]'");
        $this->addSql('ALTER TABLE announcement ALTER target_category_ids DROP DEFAULT');
        $this->addSql("ALTER TABLE announcement ADD target_product_ids JSON NOT NULL DEFAULT '[]'");
        $this->addSql('ALTER TABLE announcement ALTER target_product_ids DROP DEFAULT');
        $this->addSql("ALTER TABLE announcement ADD settings JSON NOT NULL DEFAULT '{}'");
        $this->addSql('ALTER TABLE announcement ALTER settings DROP DEFAULT');
        $this->addSql('ALTER TABLE announcement ADD views_count INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE announcement ALTER views_count DROP DEFAULT');
        $this->addSql('ALTER TABLE announcement ADD clicks_count INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE announcement ALTER clicks_count DROP DEFAULT');
        $this->addSql('ALTER TABLE announcement ADD conversion_count INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE announcement ALTER conversion_count DROP DEFAULT');
        $this->addSql('ALTER TABLE announcement ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE announcement ADD image_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91C3DA5256D FOREIGN KEY (image_id) REFERENCES media (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_4DB9D91C3DA5256D ON announcement (image_id)');
        $this->addSql('ALTER TABLE review ADD author_phone VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD title VARCHAR(255) DEFAULT NULL');
        $this->addSql("ALTER TABLE review ADD images JSON NOT NULL DEFAULT '[]'");
        $this->addSql('ALTER TABLE review ALTER images DROP DEFAULT');
        $this->addSql('ALTER TABLE review ADD verified_purchase BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE review ALTER verified_purchase DROP DEFAULT');
        $this->addSql('ALTER TABLE review ADD ip_hash VARCHAR(64) DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD user_id UUID DEFAULT NULL');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_794381C6A76ED395 ON review (user_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE announcement DROP CONSTRAINT FK_4DB9D91C3DA5256D');
        $this->addSql('DROP INDEX IDX_4DB9D91C3DA5256D');
        $this->addSql('ALTER TABLE announcement DROP subtitle');
        $this->addSql('ALTER TABLE announcement DROP button_color');
        $this->addSql('ALTER TABLE announcement DROP button_text');
        $this->addSql('ALTER TABLE announcement DROP target_category_ids');
        $this->addSql('ALTER TABLE announcement DROP target_product_ids');
        $this->addSql('ALTER TABLE announcement DROP settings');
        $this->addSql('ALTER TABLE announcement DROP views_count');
        $this->addSql('ALTER TABLE announcement DROP clicks_count');
        $this->addSql('ALTER TABLE announcement DROP conversion_count');
        $this->addSql('ALTER TABLE announcement DROP updated_at');
        $this->addSql('ALTER TABLE announcement DROP image_id');
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C6A76ED395');
        $this->addSql('DROP INDEX IDX_794381C6A76ED395');
        $this->addSql('ALTER TABLE review DROP author_phone');
        $this->addSql('ALTER TABLE review DROP title');
        $this->addSql('ALTER TABLE review DROP images');
        $this->addSql('ALTER TABLE review DROP verified_purchase');
        $this->addSql('ALTER TABLE review DROP ip_hash');
        $this->addSql('ALTER TABLE review DROP user_id');
    }
}
