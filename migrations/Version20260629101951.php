<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629101951 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment_method ADD logo VARCHAR(500) DEFAULT NULL');
        $this->addSql("ALTER TABLE payment_method ADD type VARCHAR(32) NOT NULL DEFAULT 'EXTERNAL_GATEWAY'");
        $this->addSql('ALTER TABLE payment_method ALTER type DROP DEFAULT');
        $this->addSql('ALTER TABLE payment_method ADD is_visible BOOLEAN NOT NULL DEFAULT true');
        $this->addSql('ALTER TABLE payment_method ALTER is_visible DROP DEFAULT');
        $this->addSql('ALTER TABLE promotion ADD description TEXT DEFAULT NULL');
        $this->addSql('ALTER TABLE promotion ADD priority INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE promotion ALTER priority DROP DEFAULT');
        $this->addSql('ALTER TABLE promotion ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE promotion ALTER created_at DROP DEFAULT');
        $this->addSql('ALTER TABLE promotion ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE shop_payment_method ADD display_order INT NOT NULL DEFAULT 0');
        $this->addSql('ALTER TABLE shop_payment_method ALTER display_order DROP DEFAULT');
        $this->addSql('ALTER TABLE shop_payment_method ADD minimum_amount_cents INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shop_payment_method ADD maximum_amount_cents INT DEFAULT NULL');
        $this->addSql('ALTER TABLE shop_payment_method ADD encrypted_username VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE shop_payment_method ADD encrypted_password VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE shop_payment_method ADD encrypted_api_key VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE shop_payment_method ADD encrypted_secret_key VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE shop_payment_method ADD encrypted_webhook_secret VARCHAR(1024) DEFAULT NULL');
        $this->addSql('ALTER TABLE shop_payment_method ADD is_sandbox BOOLEAN NOT NULL DEFAULT false');
        $this->addSql('ALTER TABLE shop_payment_method ALTER is_sandbox DROP DEFAULT');
        $this->addSql("ALTER TABLE shop_payment_method ADD gateway_config JSON NOT NULL DEFAULT '{}'");
        $this->addSql('ALTER TABLE shop_payment_method ALTER gateway_config DROP DEFAULT');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE payment_method DROP logo');
        $this->addSql('ALTER TABLE payment_method DROP type');
        $this->addSql('ALTER TABLE payment_method DROP is_visible');
        $this->addSql('ALTER TABLE promotion DROP description');
        $this->addSql('ALTER TABLE promotion DROP priority');
        $this->addSql('ALTER TABLE promotion DROP created_at');
        $this->addSql('ALTER TABLE promotion DROP updated_at');
        $this->addSql('ALTER TABLE shop_payment_method DROP display_order');
        $this->addSql('ALTER TABLE shop_payment_method DROP minimum_amount_cents');
        $this->addSql('ALTER TABLE shop_payment_method DROP maximum_amount_cents');
        $this->addSql('ALTER TABLE shop_payment_method DROP encrypted_username');
        $this->addSql('ALTER TABLE shop_payment_method DROP encrypted_password');
        $this->addSql('ALTER TABLE shop_payment_method DROP encrypted_api_key');
        $this->addSql('ALTER TABLE shop_payment_method DROP encrypted_secret_key');
        $this->addSql('ALTER TABLE shop_payment_method DROP encrypted_webhook_secret');
        $this->addSql('ALTER TABLE shop_payment_method DROP is_sandbox');
        $this->addSql('ALTER TABLE shop_payment_method DROP gateway_config');
    }
}
