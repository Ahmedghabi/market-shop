<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260722120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add multi-tenant suggestions, reactions, comments, categories and status history';
    }

    public function up(Schema $schema): void
    {
        $this->addSql(<<<'SQL'
            CREATE TABLE suggestion_category (
                id UUID NOT NULL,
                name VARCHAR(160) NOT NULL,
                slug VARCHAR(180) NOT NULL,
                description TEXT DEFAULT NULL,
                is_active BOOLEAN NOT NULL,
                position INT NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_suggestion_category_slug ON suggestion_category (slug)');
        $this->addSql('CREATE INDEX idx_suggestion_category_active_position ON suggestion_category (is_active, position)');

        $this->addSql(<<<'SQL'
            CREATE TABLE suggestion (
                id UUID NOT NULL,
                category_id UUID DEFAULT NULL,
                boutique_id UUID NOT NULL,
                tenant_id UUID NOT NULL,
                created_by_id UUID NOT NULL,
                official_response_by_id UUID DEFAULT NULL,
                title VARCHAR(255) NOT NULL,
                description TEXT NOT NULL,
                status VARCHAR(32) NOT NULL,
                visibility VARCHAR(16) NOT NULL,
                is_published BOOLEAN NOT NULL,
                official_response TEXT DEFAULT NULL,
                show_author_public BOOLEAN NOT NULL,
                show_boutique_public BOOLEAN NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                closed_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_suggestion_tenant_status_created ON suggestion (tenant_id, status, created_at)');
        $this->addSql('CREATE INDEX idx_suggestion_public ON suggestion (is_published, visibility, published_at)');
        $this->addSql('CREATE INDEX idx_suggestion_category ON suggestion (category_id)');
        $this->addSql('CREATE INDEX IDX_DD80F31BAB677BE6 ON suggestion (boutique_id)');
        $this->addSql('CREATE INDEX IDX_DD80F31BB03A8386 ON suggestion (created_by_id)');
        $this->addSql('CREATE INDEX IDX_DD80F31B7D0ADAF0 ON suggestion (official_response_by_id)');
        $this->addSql('ALTER TABLE suggestion ADD CONSTRAINT FK_DD80F31B12469DE2 FOREIGN KEY (category_id) REFERENCES suggestion_category (id) ON DELETE SET NULL NOT DEFERRABLE');
        $this->addSql('ALTER TABLE suggestion ADD CONSTRAINT FK_DD80F31BAB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE suggestion ADD CONSTRAINT FK_DD80F31BB03A8386 FOREIGN KEY (created_by_id) REFERENCES app_user (id) ON DELETE RESTRICT NOT DEFERRABLE');
        $this->addSql('ALTER TABLE suggestion ADD CONSTRAINT FK_DD80F31B7D0ADAF0 FOREIGN KEY (official_response_by_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE');

        $this->addSql(<<<'SQL'
            CREATE TABLE suggestion_reaction (
                id UUID NOT NULL,
                suggestion_id UUID NOT NULL,
                user_id UUID NOT NULL,
                boutique_id UUID NOT NULL,
                type VARCHAR(32) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE UNIQUE INDEX uniq_suggestion_reaction_user ON suggestion_reaction (suggestion_id, user_id)');
        $this->addSql('CREATE INDEX idx_suggestion_reaction_suggestion_type ON suggestion_reaction (suggestion_id, type)');
        $this->addSql('CREATE INDEX IDX_F3D06891A41BB822 ON suggestion_reaction (suggestion_id)');
        $this->addSql('CREATE INDEX IDX_F3D06891A76ED395 ON suggestion_reaction (user_id)');
        $this->addSql('CREATE INDEX IDX_F3D06891AB677BE6 ON suggestion_reaction (boutique_id)');
        $this->addSql('ALTER TABLE suggestion_reaction ADD CONSTRAINT FK_F3D06891A41BB822 FOREIGN KEY (suggestion_id) REFERENCES suggestion (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE suggestion_reaction ADD CONSTRAINT FK_F3D06891A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE suggestion_reaction ADD CONSTRAINT FK_F3D06891AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');

        $this->addSql(<<<'SQL'
            CREATE TABLE suggestion_comment (
                id UUID NOT NULL,
                suggestion_id UUID NOT NULL,
                user_id UUID NOT NULL,
                boutique_id UUID NOT NULL,
                parent_id UUID DEFAULT NULL,
                content TEXT NOT NULL,
                visibility VARCHAR(16) NOT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_suggestion_comment_suggestion_created ON suggestion_comment (suggestion_id, created_at)');
        $this->addSql('CREATE INDEX IDX_FBAE1118A41BB822 ON suggestion_comment (suggestion_id)');
        $this->addSql('CREATE INDEX IDX_FBAE1118A76ED395 ON suggestion_comment (user_id)');
        $this->addSql('CREATE INDEX IDX_FBAE1118AB677BE6 ON suggestion_comment (boutique_id)');
        $this->addSql('CREATE INDEX IDX_FBAE1118727ACA70 ON suggestion_comment (parent_id)');
        $this->addSql('ALTER TABLE suggestion_comment ADD CONSTRAINT FK_FBAE1118A41BB822 FOREIGN KEY (suggestion_id) REFERENCES suggestion (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE suggestion_comment ADD CONSTRAINT FK_FBAE1118A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE suggestion_comment ADD CONSTRAINT FK_FBAE1118AB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE suggestion_comment ADD CONSTRAINT FK_FBAE1118727ACA70 FOREIGN KEY (parent_id) REFERENCES suggestion_comment (id) ON DELETE CASCADE NOT DEFERRABLE');

        $this->addSql(<<<'SQL'
            CREATE TABLE suggestion_status_history (
                id UUID NOT NULL,
                suggestion_id UUID NOT NULL,
                old_status VARCHAR(32) DEFAULT NULL,
                new_status VARCHAR(32) NOT NULL,
                changed_by_id UUID DEFAULT NULL,
                comment TEXT DEFAULT NULL,
                created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
                PRIMARY KEY(id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_suggestion_history_suggestion_created ON suggestion_status_history (suggestion_id, created_at)');
        $this->addSql('CREATE INDEX IDX_1E810B99A41BB822 ON suggestion_status_history (suggestion_id)');
        $this->addSql('CREATE INDEX IDX_1E810B99828AD0A0 ON suggestion_status_history (changed_by_id)');
        $this->addSql('ALTER TABLE suggestion_status_history ADD CONSTRAINT FK_1E810B99A41BB822 FOREIGN KEY (suggestion_id) REFERENCES suggestion (id) ON DELETE CASCADE NOT DEFERRABLE');
        $this->addSql('ALTER TABLE suggestion_status_history ADD CONSTRAINT FK_1E810B99828AD0A0 FOREIGN KEY (changed_by_id) REFERENCES app_user (id) ON DELETE SET NULL NOT DEFERRABLE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE suggestion_status_history DROP CONSTRAINT FK_1E810B99A41BB822');
        $this->addSql('ALTER TABLE suggestion_status_history DROP CONSTRAINT FK_1E810B99828AD0A0');
        $this->addSql('ALTER TABLE suggestion_comment DROP CONSTRAINT FK_FBAE1118A41BB822');
        $this->addSql('ALTER TABLE suggestion_comment DROP CONSTRAINT FK_FBAE1118A76ED395');
        $this->addSql('ALTER TABLE suggestion_comment DROP CONSTRAINT FK_FBAE1118AB677BE6');
        $this->addSql('ALTER TABLE suggestion_comment DROP CONSTRAINT FK_FBAE1118727ACA70');
        $this->addSql('ALTER TABLE suggestion_reaction DROP CONSTRAINT FK_F3D06891A41BB822');
        $this->addSql('ALTER TABLE suggestion_reaction DROP CONSTRAINT FK_F3D06891A76ED395');
        $this->addSql('ALTER TABLE suggestion_reaction DROP CONSTRAINT FK_F3D06891AB677BE6');
        $this->addSql('ALTER TABLE suggestion DROP CONSTRAINT FK_DD80F31B12469DE2');
        $this->addSql('ALTER TABLE suggestion DROP CONSTRAINT FK_DD80F31BAB677BE6');
        $this->addSql('ALTER TABLE suggestion DROP CONSTRAINT FK_DD80F31BB03A8386');
        $this->addSql('ALTER TABLE suggestion DROP CONSTRAINT FK_DD80F31B7D0ADAF0');
        $this->addSql('DROP TABLE suggestion_status_history');
        $this->addSql('DROP TABLE suggestion_comment');
        $this->addSql('DROP TABLE suggestion_reaction');
        $this->addSql('DROP TABLE suggestion');
        $this->addSql('DROP TABLE suggestion_category');
    }
}
