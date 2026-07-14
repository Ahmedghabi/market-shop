<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260707120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create Loyalty Engine tables (program, rule, reward, transaction), add customer.birth_date, drop legacy BoutiqueSettings loyalty scalar fields';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("ALTER TABLE customer ADD COLUMN IF NOT EXISTS birth_date DATE DEFAULT NULL");

        // Superseded by CustomerLoyalty.pointsBalance — this column was never kept in sync by any
        // real logic (fixtures-only), and LoyaltyEngine must be the single source of truth for balances.
        $this->addSql('ALTER TABLE customer DROP COLUMN IF EXISTS loyalty_points');

        // Drop the never-fully-wired stub tables (LoyaltyAccount/old LoyaltyTransaction shape) —
        // superseded by CustomerLoyalty (balance ledger, already migrated) + the new LoyaltyTransaction below.
        $this->addSql('DROP TABLE IF EXISTS loyalty_transaction');
        $this->addSql('DROP TABLE IF EXISTS loyalty_account');

        $this->addSql("CREATE TABLE IF NOT EXISTS loyalty_program (
            id UUID NOT NULL,
            boutique_id UUID NOT NULL,
            is_active BOOLEAN NOT NULL,
            points_validity_policy VARCHAR(32) NOT NULL,
            custom_validity_days INT DEFAULT NULL,
            allow_choose_amount BOOLEAN NOT NULL,
            allow_use_all_points BOOLEAN NOT NULL,
            allow_reward_selection BOOLEAN NOT NULL,
            min_points_to_redeem INT NOT NULL,
            point_value_cents INT NOT NULL,
            max_points_per_order INT DEFAULT NULL,
            max_discount_cents_per_order INT DEFAULT NULL,
            min_order_amount_cents_to_redeem INT NOT NULL,
            min_orders_count_to_redeem INT NOT NULL,
            combinable_with_promotions BOOLEAN NOT NULL,
            combinable_with_coupons BOOLEAN NOT NULL,
            combinable_with_other_discounts BOOLEAN NOT NULL,
            combinable_with_free_shipping BOOLEAN NOT NULL,
            return_used_points_on_cancel BOOLEAN NOT NULL,
            revoke_earned_points_on_cancel BOOLEAN NOT NULL,
            calculation_order JSON NOT NULL,
            cache_ttl_seconds INT NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )");
        $this->addSql("CREATE UNIQUE INDEX IF NOT EXISTS uniq_loyalty_program_boutique ON loyalty_program (boutique_id)");

        $this->addSql("CREATE TABLE IF NOT EXISTS loyalty_reward (
            id UUID NOT NULL,
            program_id UUID NOT NULL,
            name VARCHAR(160) NOT NULL,
            description TEXT DEFAULT NULL,
            type_code VARCHAR(64) NOT NULL,
            config JSON NOT NULL,
            cost_type VARCHAR(32) NOT NULL,
            cost_value INT NOT NULL,
            min_order_amount_cents INT DEFAULT NULL,
            max_discount_cents INT DEFAULT NULL,
            min_orders_required INT DEFAULT NULL,
            validity_days INT DEFAULT NULL,
            combinable_with_promotions BOOLEAN DEFAULT NULL,
            combinable_with_coupons BOOLEAN DEFAULT NULL,
            combinable_with_other_discounts BOOLEAN DEFAULT NULL,
            combinable_with_free_shipping BOOLEAN DEFAULT NULL,
            usage_limit INT DEFAULT NULL,
            usage_limit_per_customer INT DEFAULT NULL,
            priority INT NOT NULL,
            is_active BOOLEAN NOT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_loyalty_reward_program ON loyalty_reward (program_id)");

        $this->addSql("CREATE TABLE IF NOT EXISTS loyalty_rule (
            id UUID NOT NULL,
            program_id UUID NOT NULL,
            name VARCHAR(160) NOT NULL,
            description TEXT DEFAULT NULL,
            trigger_code VARCHAR(64) NOT NULL,
            trigger_config JSON NOT NULL,
            reward_points INT NOT NULL,
            is_multiplier BOOLEAN NOT NULL,
            multiplier_value DOUBLE PRECISION NOT NULL,
            applies_to_trigger_codes JSON DEFAULT NULL,
            unlocked_reward_id UUID DEFAULT NULL,
            priority INT NOT NULL,
            is_active BOOLEAN NOT NULL,
            is_cumulative BOOLEAN NOT NULL,
            starts_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            ends_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            active_days_of_week JSON DEFAULT NULL,
            max_triggers_per_customer INT DEFAULT NULL,
            max_triggers_per_period INT DEFAULT NULL,
            period_type VARCHAR(16) DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            PRIMARY KEY(id)
        )");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_loyalty_rule_program ON loyalty_rule (program_id)");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_loyalty_rule_trigger ON loyalty_rule (trigger_code)");
        $this->addSql('ALTER TABLE loyalty_rule ADD CONSTRAINT fk_loyalty_rule_program FOREIGN KEY (program_id) REFERENCES loyalty_program (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE loyalty_rule ADD CONSTRAINT fk_loyalty_rule_unlocked_reward FOREIGN KEY (unlocked_reward_id) REFERENCES loyalty_reward (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE loyalty_reward ADD CONSTRAINT fk_loyalty_reward_program FOREIGN KEY (program_id) REFERENCES loyalty_program (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE loyalty_program ADD CONSTRAINT fk_loyalty_program_boutique FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql("CREATE TABLE IF NOT EXISTS loyalty_transaction (
            id UUID NOT NULL,
            customer_loyalty_id UUID NOT NULL,
            boutique_id UUID NOT NULL,
            type VARCHAR(32) NOT NULL,
            points INT NOT NULL,
            remaining_points INT DEFAULT NULL,
            discount_cents INT DEFAULT NULL,
            order_id UUID DEFAULT NULL,
            rule_id UUID DEFAULT NULL,
            reward_id UUID DEFAULT NULL,
            reason VARCHAR(255) DEFAULT NULL,
            expires_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
            created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
            PRIMARY KEY(id)
        )");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_loyalty_transaction_account ON loyalty_transaction (customer_loyalty_id)");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_loyalty_transaction_boutique ON loyalty_transaction (boutique_id)");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_loyalty_transaction_order ON loyalty_transaction (order_id)");
        $this->addSql("CREATE INDEX IF NOT EXISTS idx_loyalty_transaction_expires ON loyalty_transaction (expires_at)");
        $this->addSql('ALTER TABLE loyalty_transaction ADD CONSTRAINT fk_loyalty_transaction_account FOREIGN KEY (customer_loyalty_id) REFERENCES customer_loyalty (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE loyalty_transaction ADD CONSTRAINT fk_loyalty_transaction_boutique FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE loyalty_transaction ADD CONSTRAINT fk_loyalty_transaction_order FOREIGN KEY (order_id) REFERENCES customer_order (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE loyalty_transaction ADD CONSTRAINT fk_loyalty_transaction_rule FOREIGN KEY (rule_id) REFERENCES loyalty_rule (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE loyalty_transaction ADD CONSTRAINT fk_loyalty_transaction_reward FOREIGN KEY (reward_id) REFERENCES loyalty_reward (id) ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE');

        // Superseded by LoyaltyProgram / LoyaltyRule (single hardcoded rule -> fully configurable rules engine)
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS enable_loyalty');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS loyalty_points_per_amount');
        $this->addSql('ALTER TABLE boutique_settings DROP COLUMN IF EXISTS loyalty_amount_cents');
    }

    public function down(Schema $schema): void
    {
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS enable_loyalty BOOLEAN NOT NULL DEFAULT FALSE");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS loyalty_points_per_amount INT NOT NULL DEFAULT 100");
        $this->addSql("ALTER TABLE boutique_settings ADD COLUMN IF NOT EXISTS loyalty_amount_cents INT NOT NULL DEFAULT 100");

        $this->addSql('DROP TABLE IF EXISTS loyalty_transaction');
        $this->addSql('ALTER TABLE loyalty_rule DROP CONSTRAINT IF EXISTS fk_loyalty_rule_unlocked_reward');
        $this->addSql('DROP TABLE IF EXISTS loyalty_rule');
        $this->addSql('DROP TABLE IF EXISTS loyalty_reward');
        $this->addSql('DROP TABLE IF EXISTS loyalty_program');

        $this->addSql('ALTER TABLE customer DROP COLUMN IF EXISTS birth_date');
        $this->addSql('ALTER TABLE customer ADD COLUMN IF NOT EXISTS loyalty_points INT NOT NULL DEFAULT 0');
    }
}
