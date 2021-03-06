<?php

use Phinx\Migration\AbstractMigration;

class AddUserSubscriptionTable extends AbstractMigration
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
        if ($this->hasTable('user_subscription')) {
            return;
        }

        $user_subscription = $this->table('user_subscription');
        $user_subscription->addColumn('user_id', 'integer', array('limit' => 11, 'null' => false))
            ->addColumn('now_level', 'integer', array('limit' => 11, 'null' => false))
            ->addColumn('next_level', 'integer', array('limit' => 11, 'null' => false))
            ->addColumn('start_at', 'datetime')
            ->addColumn('end_at', 'datetime')
            ->addColumn('updated_at', 'datetime')
            ->addColumn('created_at', 'timestamp', array('default' => 'CURRENT_TIMESTAMP'))
            ->create();

    }
}
