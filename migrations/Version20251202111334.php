<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251202111334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add icon/logo/avatar paths for categories, brands and users';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE category ADD icon_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE brand ADD logo_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD avatar_path VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE brand SET logo_path = logo_url');
        $this->addSql('ALTER TABLE brand DROP logo_url');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE brand ADD logo_url VARCHAR(255) DEFAULT NULL');
        $this->addSql('UPDATE brand SET logo_url = logo_path');
        $this->addSql('ALTER TABLE brand DROP logo_path');
        $this->addSql('ALTER TABLE "user" DROP avatar_path');
        $this->addSql('ALTER TABLE category DROP icon_path');
    }
}
