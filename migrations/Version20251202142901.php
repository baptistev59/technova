<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251202142901 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add product attributes/values/variants tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE product_attribute (id SERIAL NOT NULL, product_id INT NOT NULL, name VARCHAR(120) NOT NULL, slug VARCHAR(120) NOT NULL, input_type VARCHAR(40) DEFAULT \'select\' NOT NULL, position SMALLINT DEFAULT 0 NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_94DA59764584665A ON product_attribute (product_id)');
        $this->addSql('ALTER TABLE product_attribute ADD CONSTRAINT FK_94DA59764584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE product_attribute_value (id SERIAL NOT NULL, attribute_id INT NOT NULL, value VARCHAR(120) NOT NULL, slug VARCHAR(120) NOT NULL, color_hex VARCHAR(7) DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_CCC4BE1FB6E62EFA ON product_attribute_value (attribute_id)');
        $this->addSql('ALTER TABLE product_attribute_value ADD CONSTRAINT FK_CCC4BE1FB6E62EFA FOREIGN KEY (attribute_id) REFERENCES product_attribute (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        $this->addSql('CREATE TABLE product_variant (id SERIAL NOT NULL, product_id INT NOT NULL, sku VARCHAR(120) DEFAULT NULL, barcode VARCHAR(120) DEFAULT NULL, price NUMERIC(10, 2) NOT NULL, promo_price NUMERIC(10, 2) DEFAULT NULL, stock INT DEFAULT 0 NOT NULL, is_available BOOLEAN DEFAULT TRUE NOT NULL, image_path VARCHAR(255) DEFAULT NULL, configuration JSON DEFAULT NULL, metadata JSON DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_209AA41D4584665A ON product_variant (product_id)');
        $this->addSql('ALTER TABLE product_variant ADD CONSTRAINT FK_209AA41D4584665A FOREIGN KEY (product_id) REFERENCES product (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE product_attribute_value DROP CONSTRAINT FK_CCC4BE1FB6E62EFA');
        $this->addSql('ALTER TABLE product_attribute DROP CONSTRAINT FK_94DA59764584665A');
        $this->addSql('ALTER TABLE product_variant DROP CONSTRAINT FK_209AA41D4584665A');
        $this->addSql('DROP TABLE product_variant');
        $this->addSql('DROP TABLE product_attribute_value');
        $this->addSql('DROP TABLE product_attribute');
    }
}
