<?php

declare(strict_types=1);

use Phinx\Db\Adapter\MysqlAdapter;
use Phinx\Migration\AbstractMigration;

final class SignupTables extends AbstractMigration
{
    public function change(): void
    {
        $this->table('ffd_student_rsvp')
            ->addColumn('uuid', 'uuid')
            ->addColumn('page_uuid', 'uuid')
            ->addColumn('for', 'string', ['length' => 50])
            ->addColumn('first_name', 'string', ['length' => 100])
            ->addColumn('last_name', 'string', ['length' => 100])
            ->addColumn('shirt', 'string', ['length' => 3])
            ->addColumn('guest_count', 'integer')
            ->addColumn('guest_email', 'string', ['length' => 250, 'null' => true])
            ->addColumn('alumni_parents', 'string', ['length' => 250, 'null' => true])
            ->addColumn('accommodations', 'text', ['limit' => MysqlAdapter::TEXT_REGULAR, 'null' => true])
            ->addColumn('cancelled', 'integer', ['null' => true])
            ->addColumn('cancelled_by', 'uuid', ['null' => true])
            ->addColumn('created', 'integer')
            ->addColumn('created_by', 'uuid')
            ->addColumn('updated', 'integer')
            ->addColumn('updated_by', 'uuid')
            ->addForeignKey(['page_uuid'], 'page', ['uuid'])
            ->addForeignKey(['cancelled_by'], 'user', ['uuid'])
            ->addForeignKey(['created_by'], 'user', ['uuid'])
            ->addForeignKey(['updated_by'], 'user', ['uuid'])
            ->addIndex('page_uuid')
            ->addIndex('for')
            ->addIndex(['page_uuid', 'for'], ['unique' => true])
            ->addIndex('cancelled')
            ->addIndex('created')
            ->addIndex('updated')
            ->create();
        $this->table('ffd_faculty_rsvp')
            ->addColumn('uuid', 'uuid')
            ->addColumn('page_uuid', 'uuid')
            ->addColumn('for', 'string', ['length' => 50])
            ->addColumn('first_name', 'string', ['length' => 100])
            ->addColumn('last_name', 'string', ['length' => 100])
            ->addColumn('regalia_requested', 'boolean')
            ->addColumn('accommodations', 'text', ['limit' => MysqlAdapter::TEXT_REGULAR, 'null' => true])
            ->addColumn('cancelled', 'integer', ['null' => true])
            ->addColumn('cancelled_by', 'uuid', ['null' => true])
            ->addColumn('created', 'integer')
            ->addColumn('created_by', 'uuid')
            ->addColumn('updated', 'integer')
            ->addColumn('updated_by', 'uuid')
            ->addForeignKey(['page_uuid'], 'page', ['uuid'])
            ->addForeignKey(['cancelled_by'], 'user', ['uuid'])
            ->addForeignKey(['created_by'], 'user', ['uuid'])
            ->addForeignKey(['updated_by'], 'user', ['uuid'])
            ->addIndex('page_uuid')
            ->addIndex('for')
            ->addIndex('regalia_requested')
            ->addIndex(['page_uuid', 'for'], ['unique' => true])
            ->addIndex('cancelled')
            ->addIndex('created')
            ->addIndex('updated')
            ->create();
    }
}
