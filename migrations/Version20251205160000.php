<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251205160000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Commande client + lignes de commande';
    }

    public function up(Schema $schema): void
    {
        if (!$schema->hasTable('customer_order')) {
            $orderTable = $schema->createTable('customer_order');
            $orderTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $orderTable->addColumn('reference', 'string', ['length' => 40]);
            $orderTable->addColumn('owner_id', 'integer', ['notnull' => false]);
            $orderTable->addColumn('status', 'string', ['length' => 20]);
            $orderTable->addColumn('total_amount', 'decimal', ['precision' => 10, 'scale' => 2]);
            $orderTable->addColumn('currency', 'string', ['length' => 3]);
            $orderTable->addColumn('shipping_address', 'json', []);
            $orderTable->addColumn('billing_address', 'json', ['notnull' => false]);
            $orderTable->addColumn('paid_at', 'datetime_immutable', ['notnull' => false]);
            $orderTable->addColumn('created_at', 'datetime_immutable');
            $orderTable->addColumn('updated_at', 'datetime_immutable');
            $orderTable->setPrimaryKey(['id']);
            $orderTable->addUniqueIndex(['reference'], 'UNIQ_CUSTOMER_ORDER_REFERENCE');
            $orderTable->addForeignKeyConstraint('user', ['owner_id'], ['id'], ['onDelete' => 'SET NULL'], 'FK_ORDER_OWNER');
        } else {
            $orderTable = $schema->getTable('customer_order');
            if (!$orderTable->hasColumn('paid_at')) {
                $orderTable->addColumn('paid_at', 'datetime_immutable', ['notnull' => false]);
            }
        }

        if (!$schema->hasTable('customer_order_item')) {
            $itemTable = $schema->createTable('customer_order_item');
            $itemTable->addColumn('id', 'integer', ['autoincrement' => true]);
            $itemTable->addColumn('customer_order_id', 'integer');
            $itemTable->addColumn('product_id', 'integer');
            $itemTable->addColumn('product_name', 'string', ['length' => 255]);
            $itemTable->addColumn('unit_price', 'decimal', ['precision' => 10, 'scale' => 2]);
            $itemTable->addColumn('line_total', 'decimal', ['precision' => 10, 'scale' => 2]);
            $itemTable->addColumn('quantity', 'integer');
            $itemTable->addColumn('product_image', 'string', ['length' => 255, 'notnull' => false]);
            $itemTable->setPrimaryKey(['id']);
            $itemTable->addIndex(['customer_order_id'], 'IDX_ORDER_ITEM_ORDER');
            $itemTable->addForeignKeyConstraint('customer_order', ['customer_order_id'], ['id'], ['onDelete' => 'CASCADE'], 'FK_ORDER_ITEM_ORDER');
        }
    }

    public function down(Schema $schema): void
    {
        if ($schema->hasTable('customer_order_item')) {
            $schema->dropTable('customer_order_item');
        }

        if ($schema->hasTable('customer_order')) {
            $schema->dropTable('customer_order');
        }
    }
}
