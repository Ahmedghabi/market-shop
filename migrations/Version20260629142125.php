<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629142125 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE audit_log (actor_email VARCHAR(180) NOT NULL, actor_role VARCHAR(32) NOT NULL, action VARCHAR(80) NOT NULL, resource_type VARCHAR(80) NOT NULL, resource_id VARCHAR(36) DEFAULT NULL, details JSON DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, boutique_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_F6E1C0F5AB677BE6 ON audit_log (boutique_id)');
        $this->addSql('CREATE INDEX idx_audit_log_created ON audit_log (created_at)');
        $this->addSql('CREATE TABLE coupon (code VARCHAR(64) NOT NULL, name VARCHAR(160) NOT NULL, type VARCHAR(32) NOT NULL, scope VARCHAR(32) NOT NULL, value INT NOT NULL, max_discount_cents INT DEFAULT NULL, min_cart_amount_cents INT NOT NULL, max_cart_amount_cents INT DEFAULT NULL, usage_limit INT NOT NULL, used_count INT NOT NULL, per_user_limit INT DEFAULT NULL, combine_with_promotions BOOLEAN NOT NULL, is_active BOOLEAN NOT NULL, starts_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, buy_xget_yconfig JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, boutique_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_64BF3F02AB677BE6 ON coupon (boutique_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_coupon_code_boutique ON coupon (boutique_id, code)');
        $this->addSql('CREATE TABLE coupon_category (id UUID NOT NULL, coupon_id UUID NOT NULL, category_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_1FCF47F366C5951B ON coupon_category (coupon_id)');
        $this->addSql('CREATE INDEX IDX_1FCF47F312469DE2 ON coupon_category (category_id)');
        $this->addSql('CREATE TABLE coupon_product (id UUID NOT NULL, coupon_id UUID NOT NULL, product_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_3C22473B66C5951B ON coupon_product (coupon_id)');
        $this->addSql('CREATE INDEX IDX_3C22473B4584665A ON coupon_product (product_id)');
        $this->addSql('CREATE TABLE customer_notification (type VARCHAR(80) NOT NULL, title VARCHAR(160) NOT NULL, message TEXT NOT NULL, related_order_id VARCHAR(36) DEFAULT NULL, related_shipment_id VARCHAR(36) DEFAULT NULL, is_read BOOLEAN NOT NULL, read_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, id UUID NOT NULL, customer_id UUID NOT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_B18CB5D39395C3F3 ON customer_notification (customer_id)');
        $this->addSql('CREATE INDEX idx_cust_notif_user_read ON customer_notification (customer_id, is_read)');
        $this->addSql('CREATE TABLE refund (refund_number VARCHAR(32) NOT NULL, type VARCHAR(32) NOT NULL, status VARCHAR(32) NOT NULL, currency VARCHAR(3) NOT NULL, subtotal_cents INT NOT NULL, tax_cents INT NOT NULL, total_cents INT NOT NULL, reason TEXT DEFAULT NULL, processed_by VARCHAR(180) DEFAULT NULL, processed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, boutique_id UUID NOT NULL, order_id UUID NOT NULL, customer_id UUID DEFAULT NULL, credit_note_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_5B2C1458AB677BE6 ON refund (boutique_id)');
        $this->addSql('CREATE INDEX IDX_5B2C14588D9F6D38 ON refund (order_id)');
        $this->addSql('CREATE INDEX IDX_5B2C14589395C3F3 ON refund (customer_id)');
        $this->addSql('CREATE INDEX IDX_5B2C14581C696F7A ON refund (credit_note_id)');
        $this->addSql('CREATE UNIQUE INDEX uniq_refund_number ON refund (refund_number)');
        $this->addSql('CREATE TABLE refund_item (product_name VARCHAR(180) NOT NULL, quantity INT NOT NULL, unit_price_cents INT NOT NULL, total_cents INT NOT NULL, id UUID NOT NULL, refund_id UUID NOT NULL, order_item_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_FD3E2ADC189801D5 ON refund_item (refund_id)');
        $this->addSql('CREATE INDEX IDX_FD3E2ADCE415FB15 ON refund_item (order_item_id)');
        $this->addSql('CREATE TABLE webhook (url VARCHAR(255) NOT NULL, events JSON NOT NULL, secret VARCHAR(255) DEFAULT NULL, status VARCHAR(32) NOT NULL, last_triggered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, failure_count INT DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, id UUID NOT NULL, deleted_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, boutique_id UUID DEFAULT NULL, PRIMARY KEY (id))');
        $this->addSql('CREATE INDEX IDX_8A741756AB677BE6 ON webhook (boutique_id)');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F5AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE coupon ADD CONSTRAINT FK_64BF3F02AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE coupon_category ADD CONSTRAINT FK_1FCF47F366C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE coupon_category ADD CONSTRAINT FK_1FCF47F312469DE2 FOREIGN KEY (category_id) REFERENCES category (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE coupon_product ADD CONSTRAINT FK_3C22473B66C5951B FOREIGN KEY (coupon_id) REFERENCES coupon (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE coupon_product ADD CONSTRAINT FK_3C22473B4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE customer_notification ADD CONSTRAINT FK_B18CB5D39395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE refund ADD CONSTRAINT FK_5B2C1458AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE refund ADD CONSTRAINT FK_5B2C14588D9F6D38 FOREIGN KEY (order_id) REFERENCES customer_order (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE refund ADD CONSTRAINT FK_5B2C14589395C3F3 FOREIGN KEY (customer_id) REFERENCES customer (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE refund ADD CONSTRAINT FK_5B2C14581C696F7A FOREIGN KEY (credit_note_id) REFERENCES invoice (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE refund_item ADD CONSTRAINT FK_FD3E2ADC189801D5 FOREIGN KEY (refund_id) REFERENCES refund (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE refund_item ADD CONSTRAINT FK_FD3E2ADCE415FB15 FOREIGN KEY (order_item_id) REFERENCES order_item (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE webhook ADD CONSTRAINT FK_8A741756AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE audit_log DROP CONSTRAINT FK_F6E1C0F5AB677BE6');
        $this->addSql('ALTER TABLE coupon DROP CONSTRAINT FK_64BF3F02AB677BE6');
        $this->addSql('ALTER TABLE coupon_category DROP CONSTRAINT FK_1FCF47F366C5951B');
        $this->addSql('ALTER TABLE coupon_category DROP CONSTRAINT FK_1FCF47F312469DE2');
        $this->addSql('ALTER TABLE coupon_product DROP CONSTRAINT FK_3C22473B66C5951B');
        $this->addSql('ALTER TABLE coupon_product DROP CONSTRAINT FK_3C22473B4584665A');
        $this->addSql('ALTER TABLE customer_notification DROP CONSTRAINT FK_B18CB5D39395C3F3');
        $this->addSql('ALTER TABLE refund DROP CONSTRAINT FK_5B2C1458AB677BE6');
        $this->addSql('ALTER TABLE refund DROP CONSTRAINT FK_5B2C14588D9F6D38');
        $this->addSql('ALTER TABLE refund DROP CONSTRAINT FK_5B2C14589395C3F3');
        $this->addSql('ALTER TABLE refund DROP CONSTRAINT FK_5B2C14581C696F7A');
        $this->addSql('ALTER TABLE refund_item DROP CONSTRAINT FK_FD3E2ADC189801D5');
        $this->addSql('ALTER TABLE refund_item DROP CONSTRAINT FK_FD3E2ADCE415FB15');
        $this->addSql('ALTER TABLE webhook DROP CONSTRAINT FK_8A741756AB677BE6');
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('DROP TABLE coupon');
        $this->addSql('DROP TABLE coupon_category');
        $this->addSql('DROP TABLE coupon_product');
        $this->addSql('DROP TABLE customer_notification');
        $this->addSql('DROP TABLE refund');
        $this->addSql('DROP TABLE refund_item');
        $this->addSql('DROP TABLE webhook');
    }
}
