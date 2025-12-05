<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251204214927 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Création de la table saved_cart pour persister le panier entre deux sessions.';
    }

    public function up(Schema $schema): void
    {
        if ($schema->hasTable('saved_cart')) {
            return;
        }

        $table = $schema->createTable('saved_cart');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('owner_id', 'integer', ['notnull' => true]);
        $table->addColumn('items', 'json', ['notnull' => true]);
        $table->addColumn('updated_at', 'datetime_immutable', ['notnull' => true]);
        $table->setPrimaryKey(['id']);
        $table->addUniqueIndex(['owner_id'], 'UNIQ_SAVED_CART_OWNER');
        $table->addForeignKeyConstraint('user', ['owner_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_SAVED_CART_OWNER');
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('saved_cart')) {
            $schema->dropTable('saved_cart');
        }
    }
}
