<?php

use Phpmig\Migration\Migration;

class CreateCreatorTables extends Migration
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
        $this->schema->create('creators', function($table){
            $table->increments('id');
            $table->string('title', 80);
            $table->string('type', 80);
            $table->string('screenshot', 255)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        $this->schema->create('creator_meta', function($table){
            $table->increments('id');
            $table->integer('creator_id')->unsigned();
            $table->string('meta_key', 80)->nullable();
            $table->text('meta_value')->nullable();

            $table->foreign('creator_id')->references('id')->on('creators');
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->schema->drop('creators');
        $this->schema->drop('creator_meta');
    }
}
