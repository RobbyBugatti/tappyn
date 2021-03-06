<?php

use Phinx\Migration\AbstractMigration;
use Phinx\Db\Adapter\MysqlAdapter;
class CreateContactsTable extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * http://docs.phinx.org/en/latest/migrations.html#the-abstractmigration-class
     *
     * The following commands can be used in this method and Phinx will
     * automatically reverse them when rolling back:
     *
     *    createTable
     *    renameTable
     *    addColumn
     *    renameColumn
     *    addIndex
     *    addForeignKey
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change()
    {
        if($this->hasTable('contacts')) return;
            $contacts = $this->table('contacts');
            $contacts->addColumn('created_at', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
                     ->addColumn('customer', 'string', array('limit' => 45, 'null' => FALSE))
                     ->addColumn('email', 'string', array('limit' => 100, 'null' => FALSE))
                     ->addColumn('subject', 'string', array('limit' => 100, 'null' => FALSE))
                     ->addColumn('topic', 'string', array('limit' => 100, 'null' => FALSE))
                     ->addColumn('message', 'text', array('null' => FALSE))
                     ->create();

    }
}
