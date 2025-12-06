<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251206122647 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Ajout de la colonne is_deleted sur user';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable('user');
        if (!$table->hasColumn('is_deleted')) {
            $table->addColumn('is_deleted', 'boolean', ['default' => false]);
        }
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable('user');
        if ($table->hasColumn('is_deleted')) {
            $table->dropColumn('is_deleted');
        }
    }
}
