<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251120125446 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP SEQUENCE messenger_messages_id_seq CASCADE');
        $this->addSql('CREATE TABLE audit_log (id SERIAL NOT NULL, owner_id INT DEFAULT NULL, action VARCHAR(255) NOT NULL, resource VARCHAR(255) DEFAULT NULL, resource_id INT DEFAULT NULL, ip_address VARCHAR(45) DEFAULT NULL, user_agent TEXT DEFAULT NULL, data JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_F6E1C0F57E3C61F9 ON audit_log (owner_id)');
        $this->addSql('COMMENT ON COLUMN audit_log.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE audit_log ADD CONSTRAINT FK_F6E1C0F57E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE address ADD owner_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE address ADD CONSTRAINT FK_D4E6F817E3C61F9 FOREIGN KEY (owner_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX IDX_D4E6F817E3C61F9 ON address (owner_id)');
        $this->addSql('ALTER TABLE "user" ADD vendor_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD CONSTRAINT FK_8D93D649F603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649F603EE73 ON "user" (vendor_id)');
        $this->addSql('ALTER TABLE vendor ADD address_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE vendor ADD business_id_type VARCHAR(50) DEFAULT NULL');
        $this->addSql('ALTER TABLE vendor ADD email VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE vendor ADD website VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE vendor ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('ALTER TABLE vendor ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL');
        $this->addSql('COMMENT ON COLUMN vendor.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN vendor.updated_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE vendor ADD CONSTRAINT FK_F52233F6F5B7AF75 FOREIGN KEY (address_id) REFERENCES address (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_F52233F6F5B7AF75 ON vendor (address_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('CREATE SEQUENCE messenger_messages_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_75ea56e016ba31db ON messenger_messages (delivered_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0e3bd61ce ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX idx_75ea56e0fb7336f0 ON messenger_messages (queue_name)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('ALTER TABLE audit_log DROP CONSTRAINT FK_F6E1C0F57E3C61F9');
        $this->addSql('DROP TABLE audit_log');
        $this->addSql('ALTER TABLE address DROP CONSTRAINT FK_D4E6F817E3C61F9');
        $this->addSql('DROP INDEX IDX_D4E6F817E3C61F9');
        $this->addSql('ALTER TABLE address DROP owner_id');
        $this->addSql('ALTER TABLE vendor DROP CONSTRAINT FK_F52233F6F5B7AF75');
        $this->addSql('DROP INDEX UNIQ_F52233F6F5B7AF75');
        $this->addSql('ALTER TABLE vendor DROP address_id');
        $this->addSql('ALTER TABLE vendor DROP business_id_type');
        $this->addSql('ALTER TABLE vendor DROP email');
        $this->addSql('ALTER TABLE vendor DROP website');
        $this->addSql('ALTER TABLE vendor DROP created_at');
        $this->addSql('ALTER TABLE vendor DROP updated_at');
        $this->addSql('ALTER TABLE "user" DROP CONSTRAINT FK_8D93D649F603EE73');
        $this->addSql('DROP INDEX UNIQ_8D93D649F603EE73');
        $this->addSql('ALTER TABLE "user" DROP vendor_id');
    }
}
