<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260623000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add boutique validation workflow, local auth fields, administered boutiques and notifications.';
    }

    public function up(Schema $schema): void
    {
        unset($schema);

        $this->addSql('ALTER TABLE boutique ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE boutique ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD password_hash VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE app_user ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE app_user ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL');
        $this->addSql('CREATE TABLE user_administered_boutique (user_id UUID NOT NULL, boutique_id UUID NOT NULL, PRIMARY KEY(user_id, boutique_id))');
        $this->addSql('CREATE INDEX IDX_5F8E9C60A76ED395 ON user_administered_boutique (user_id)');
        $this->addSql('CREATE INDEX IDX_5F8E9C60B8F4C25C ON user_administered_boutique (boutique_id)');
        $this->addSql('ALTER TABLE user_administered_boutique ADD CONSTRAINT FK_5F8E9C60A76ED395 FOREIGN KEY (user_id) REFERENCES app_user (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_administered_boutique ADD CONSTRAINT FK_5F8E9C60B8F4C25C FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('INSERT INTO user_administered_boutique (user_id, boutique_id) SELECT id, boutique_id FROM app_user WHERE boutique_id IS NOT NULL ON CONFLICT DO NOTHING');
        $this->addSql('CREATE TABLE notification (id UUID NOT NULL, boutique_id UUID DEFAULT NULL, recipient_identifier VARCHAR(180) DEFAULT NULL, type VARCHAR(80) NOT NULL, title VARCHAR(180) NOT NULL, message TEXT NOT NULL, read BOOLEAN NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_BF5476CAA76ED395 ON notification (boutique_id)');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (boutique_id) REFERENCES boutique (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        unset($schema);

        $this->addSql('ALTER TABLE notification DROP CONSTRAINT FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE user_administered_boutique DROP CONSTRAINT FK_5F8E9C60A76ED395');
        $this->addSql('ALTER TABLE user_administered_boutique DROP CONSTRAINT FK_5F8E9C60B8F4C25C');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE user_administered_boutique');
        $this->addSql('ALTER TABLE app_user DROP password_hash');
        $this->addSql('ALTER TABLE app_user DROP created_at');
        $this->addSql('ALTER TABLE app_user DROP updated_at');
        $this->addSql('ALTER TABLE boutique DROP created_at');
        $this->addSql('ALTER TABLE boutique DROP updated_at');
    }
}
