<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260629092317 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE cms_block (
              type VARCHAR(32) NOT NULL,
              title VARCHAR(255) DEFAULT NULL,
              content TEXT DEFAULT NULL,
              settings JSON DEFAULT NULL,
              sort_order INT NOT NULL,
              is_active BOOLEAN NOT NULL,
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              id UUID NOT NULL,
              page_id UUID NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX idx_cms_block_page ON cms_block (page_id)');
        $this->addSql(<<<'SQL'
            CREATE TABLE cms_page (
              title VARCHAR(255) NOT NULL,
              slug VARCHAR(255) NOT NULL,
              type VARCHAR(32) NOT NULL,
              status VARCHAR(32) NOT NULL,
              description TEXT DEFAULT NULL,
              content TEXT DEFAULT NULL,
              template VARCHAR(255) DEFAULT NULL,
              is_homepage BOOLEAN NOT NULL,
              show_in_header BOOLEAN NOT NULL,
              show_in_footer BOOLEAN NOT NULL,
              sort_order INT NOT NULL,
              published_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              meta_title VARCHAR(255) DEFAULT NULL,
              meta_description VARCHAR(500) DEFAULT NULL,
              meta_keywords VARCHAR(500) DEFAULT NULL,
              og_title VARCHAR(255) DEFAULT NULL,
              og_description VARCHAR(500) DEFAULT NULL,
              og_image VARCHAR(500) DEFAULT NULL,
              canonical_url VARCHAR(500) DEFAULT NULL,
              created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL,
              updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL,
              id UUID NOT NULL,
              boutique_id UUID NOT NULL,
              PRIMARY KEY (id)
            )
        SQL);
        $this->addSql('CREATE INDEX IDX_D39C1B5DAB677BE6 ON cms_page (boutique_id)');
        $this->addSql('CREATE INDEX idx_cms_page_boutique_status ON cms_page (boutique_id, status)');
        $this->addSql('CREATE UNIQUE INDEX uniq_cms_page_boutique_slug ON cms_page (boutique_id, slug)');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              cms_block
            ADD
              CONSTRAINT FK_AD680C0EC4663E4 FOREIGN KEY (page_id) REFERENCES cms_page (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              cms_page
            ADD
              CONSTRAINT FK_D39C1B5DAB677BE6 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE cms_block DROP CONSTRAINT FK_AD680C0EC4663E4');
        $this->addSql('ALTER TABLE cms_page DROP CONSTRAINT FK_D39C1B5DAB677BE6');
        $this->addSql('DROP TABLE cms_block');
        $this->addSql('DROP TABLE cms_page');
    }
}
