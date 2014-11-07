<?php

use Phpmig\Migration\Migration;

class CreateContactTables extends Migration
{
    /* @var \Illuminate\Database\Schema\Builder $schema */
    protected $schema;
    
    public function init()
    {
        $this->schema = $this->get('schema');
    }

    /**
     * Do the migration
     */
    public function up()
    {
        $this->schema->create('contacts', function($table){
            $table->increments('id');
            $table->string('firstname', 255);
            $table->string('lastname', 255);
            $table->string('email', 255)->unique();
            $table->string('phone', 255)->nullable();
            $table->integer('favorite')->default(1);
            $table->timestamps();
        });

        $this->schema->create('notes', function($table){
            $table->increments('id');
            $table->text('body');
            $table->integer('contact_id')->unsigned();
            $table->timestamps();
            $table->foreign('contact_id')->references('id')->on('contacts');
        });

        $this->schema->create('sub_notes', function($table){
            $table->increments('id');
            $table->string('meta_key');
            $table->text('meta_value');
            $table->integer('note_id')->unsigned();
            $table->foreign('note_id')->references('id')->on('notes');
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->schema->drop('contacts');
        $this->schema->drop('notes');
        $this->schema->drop('sub_notes');
    }
}
