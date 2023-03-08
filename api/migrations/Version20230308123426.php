<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230308123426 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE review ADD self_ref_id UUID DEFAULT NULL');
        $this->addSql('COMMENT ON COLUMN review.self_ref_id IS \'(DC2Type:uuid)\'');
        $this->addSql('ALTER TABLE review ADD CONSTRAINT FK_794381C6D2B2E8F2 FOREIGN KEY (self_ref_id) REFERENCES review (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_794381C6D2B2E8F2 ON review (self_ref_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE review DROP CONSTRAINT FK_794381C6D2B2E8F2');
        $this->addSql('DROP INDEX IDX_794381C6D2B2E8F2');
        $this->addSql('ALTER TABLE review DROP self_ref_id');
    }
}
