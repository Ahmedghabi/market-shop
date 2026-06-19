<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260624092320 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE announcement (content TEXT NOT NULL, display_type VARCHAR(20) NOT NULL, background_color VARCHAR(32) DEFAULT NULL, text_color VARCHAR(32) DEFAULT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, starts_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, ends_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_4DB9D91CAB677BE6 ON announcement (boutique_id)');
        $this->addSql('CREATE TABLE conversation (guest_name VARCHAR(180) DEFAULT NULL, guest_email VARCHAR(180) DEFAULT NULL, guest_phone VARCHAR(64) DEFAULT NULL, active BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, user_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_conversation_boutique ON conversation (boutique_id)');
        $this->addSql('CREATE INDEX idx_conversation_user ON conversation (user_id)');
        $this->addSql('CREATE TABLE message (sender_type VARCHAR(20) NOT NULL, content TEXT NOT NULL, file_url VARCHAR(255) DEFAULT NULL, file_type VARCHAR(20) DEFAULT NULL, read BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, conversation_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX idx_message_conversation ON message (conversation_id)');
        $this->addSql('CREATE TABLE product_filter (name VARCHAR(80) NOT NULL, slug VARCHAR(80) NOT NULL, type VARCHAR(30) NOT NULL, position INT NOT NULL, active BOOLEAN NOT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1DB81EB9AB677BE6 ON product_filter (boutique_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_filter_boutique_slug ON product_filter (boutique_id, slug)');
        $this->addSql('CREATE TABLE product_filter_value (value VARCHAR(120) NOT NULL, id UUID NOT NULL, product_filter_id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B96289A0BD3504D3 ON product_filter_value (product_filter_id)');
        $this->addSql('CREATE INDEX IDX_B96289A04584665A ON product_filter_value (product_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_filter_value_product ON product_filter_value (product_filter_id, product_id, value)');
        $this->addSql('ALTER TABLE announcement ADD CONSTRAINT FK_4DB9D91CAB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE conversation ADD CONSTRAINT FK_8A8E26E9A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE message ADD CONSTRAINT FK_B6BD307F9AC0396 FOREIGN KEY (conversation_id) REFERENCES conversation (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_filter ADD CONSTRAINT FK_1DB81EB9AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_filter_value ADD CONSTRAINT FK_B96289A0BD3504D3 FOREIGN KEY (product_filter_id) REFERENCES product_filter (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE product_filter_value ADD CONSTRAINT FK_B96289A04584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE app_user ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER INDEX idx_5f8e9c60a76ed395 RENAME TO IDX_26A5873EA76ED395');
        $this->addSql('ALTER INDEX idx_5f8e9c60b8f4c25c RENAME TO IDX_26A5873EAB677BE6');
        $this->addSql('ALTER TABLE boutique ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER INDEX idx_ba388b7a76ed395 RENAME TO IDX_BA388B7AB677BE6');
        $this->addSql('ALTER INDEX idx_bf5476caa76ed395 RENAME TO IDX_BF5476CAAB677BE6');
        $this->addSql('ALTER INDEX idx_subscription_boutique RENAME TO IDX_A3C664D3AB677BE6');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE announcement DROP CONSTRAINT FK_4DB9D91CAB677BE6');
        $this->addSql('ALTER TABLE conversation DROP CONSTRAINT FK_8A8E26E9AB677BE6');
        $this->addSql('ALTER TABLE conversation DROP CONSTRAINT FK_8A8E26E9A76ED395');
        $this->addSql('ALTER TABLE message DROP CONSTRAINT FK_B6BD307F9AC0396');
        $this->addSql('ALTER TABLE product_filter DROP CONSTRAINT FK_1DB81EB9AB677BE6');
        $this->addSql('ALTER TABLE product_filter_value DROP CONSTRAINT FK_B96289A0BD3504D3');
        $this->addSql('ALTER TABLE product_filter_value DROP CONSTRAINT FK_B96289A04584665A');
        $this->addSql('DROP TABLE announcement');
        $this->addSql('DROP TABLE conversation');
        $this->addSql('DROP TABLE message');
        $this->addSql('DROP TABLE product_filter');
        $this->addSql('DROP TABLE product_filter_value');
        $this->addSql('ALTER TABLE app_user ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE boutique ALTER created_at SET DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER INDEX idx_ba388b7ab677be6 RENAME TO idx_ba388b7a76ed395');
        $this->addSql('ALTER INDEX idx_bf5476caab677be6 RENAME TO idx_bf5476caa76ed395');
        $this->addSql('ALTER INDEX idx_a3c664d3ab677be6 RENAME TO idx_subscription_boutique');
        $this->addSql('ALTER INDEX idx_26a5873ea76ed395 RENAME TO idx_5f8e9c60a76ed395');
        $this->addSql('ALTER INDEX idx_26a5873eab677be6 RENAME TO idx_5f8e9c60b8f4c25c');
    }
}
