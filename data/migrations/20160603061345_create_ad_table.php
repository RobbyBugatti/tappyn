<?php

use Phinx\Migration\AbstractMigration;

class CreateAdTable extends AbstractMigration
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
        if ($this->hasTable('ads')) {
            return;
        }

        $ads = $this->table('ads');
        $ads->addColumn('contest_id', 'integer', array('limit' => 11, 'null' => false))
            ->addColumn('submission_id', 'integer', array('limit' => 11, 'null' => false))
            ->addColumn('platform', 'string', array('limit' => 45, 'null' => false))
            ->addColumn('get_id', 'string', array('limit' => 100, 'null' => false))
            ->addColumn('content', 'text', array('limit' => 100, 'null' => true))
            ->addColumn('created_at', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
            ->create();
    }
}
