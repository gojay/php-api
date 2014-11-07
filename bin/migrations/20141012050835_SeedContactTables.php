<?php

use Phpmig\Migration\Migration;

class SeedContactTables extends Migration
{
    protected $_db;

    public function init()
    {
        $container = $this->getContainer(); 
        $this->_db = $container['db'];
    }
    
    /**
     * Do the migration
     */
    public function up()
    {
        $contacts = file_get_contents(
            dirname(__FILE__) . '/../../share/sql/data/contacts.sql'
        );
        $this->_db->exec($contacts);
    }

    /**
     * Undo the migration
     */
    public function down()
    {

    }
}
