<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251202213000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add newsletter_opt_in flag on user profile';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" ADD newsletter_opt_in BOOLEAN DEFAULT FALSE NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE "user" DROP newsletter_opt_in');
    }
}
