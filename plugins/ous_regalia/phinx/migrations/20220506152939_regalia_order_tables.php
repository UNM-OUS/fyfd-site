<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class RegaliaOrderTables extends AbstractMigration
{
    public function change(): void
    {
        $this->table('regalia_group')
            ->addColumn('name', 'string', ['length' => 100])
            ->addColumn('extra_deadline', 'integer', ['null' => true])
            ->addColumn('final_deadline', 'integer')
            ->addColumn('data', 'text', ['limit' => MysqlAdapter::TEXT_REGULAR])
            ->addIndex('name')
            ->addIndex('extra_deadline')
            ->addIndex('final_deadline')
            ->create();
        $this->table('regalia_order')
            ->addColumn('group_id', 'integer')
            ->addColumn('label', 'string', ['length' => 100])
            ->addColumn('data', 'text', ['limit' => MysqlAdapter::TEXT_REGULAR])
            ->addColumn('cancelled', 'integer', ['null' => true])
            ->addColumn('cancelled_by', 'uuid', ['null' => true])
            ->addColumn('created', 'integer')
            ->addColumn('created_by', 'uuid')
            ->addColumn('updated', 'integer')
            ->addColumn('updated_by', 'uuid')
            ->addForeignKey(['group_id'], 'regalia_group', ['id'])
            ->addForeignKey(['cancelled_by'], 'user', ['uuid'])
            ->addForeignKey(['created_by'], 'user', ['uuid'])
            ->addForeignKey(['updated_by'], 'user', ['uuid'])
            ->addIndex('cancelled')
            ->addIndex('created')
            ->addIndex('updated')
            ->create();
    }
}
