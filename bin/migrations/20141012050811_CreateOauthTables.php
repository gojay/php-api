<?php

use Phpmig\Migration\Migration;

class CreateOauthTables extends Migration
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
        $this->schema->create('oauth_scopes', function($table){
            $table->string('scope', 80);
            $table->boolean('is_default')->nullable();
        });

        $this->schema->create('oauth_users', function($table){
            $table->increments('user_id');
            $table->string('username', 80)->unique();
            $table->string('password', 80);
            $table->string('firstname', 80)->nullable();
            $table->string('lastname', 80)->nullable();
            $table->string('email', 255)->unique();
            $table->boolean('email_verified')->default(true);
        });

        $this->schema->create('oauth_clients', function($table){
            $table->string('client_id', 80);
            $table->string('client_secret', 80);
            $table->string('redirect_uri', 2000)->nullable();
            $table->string('grant_types', 80)->nullable();
            $table->string('scope', 4000)->nullable();
            $table->string('public_key', 2000)->nullable();
            $table->integer('user_id')->unsigned();

            $table->primary('client_id');
            $table->foreign('client_id')->references('client_id')->on('oauth_clients');
            $table->foreign('user_id')->references('user_id')->on('oauth_users');
        });
        
        $this->schema->create('oauth_access_tokens', function($table){
            $table->string('access_token', 40);
            $table->string('client_id', 80);
            $table->integer('user_id')->unsigned();
            $table->timestamp('expires');
            $table->string('scope', 4000)->nullable();

            $table->primary('access_token');
            $table->foreign('client_id')->references('client_id')->on('oauth_clients');
            $table->foreign('user_id')->references('user_id')->on('oauth_users');
        });

        $this->schema->create('oauth_authorization_codes', function($table){
            $table->string('authorization_code', 40);
            $table->string('client_id', 80);
            $table->integer('user_id')->unsigned();
            $table->string('redirect_uri', 2000)->nullable();
            $table->string('scope', 4000)->nullable();
            $table->timestamp('expires');
            
            $table->primary('authorization_code');
        });

        $this->schema->create('oauth_jwt', function($table){
            $table->string('client_id', 80);
            $table->string('subject', 80);
            $table->string('public_key', 2000);
            
            $table->foreign('client_id')->references('client_id')->on('oauth_clients');
        });

        $this->schema->create('oauth_refresh_tokens', function($table){
            $table->string('refresh_token', 40);
            $table->string('client_id', 80);
            $table->integer('user_id')->unsigned();
            $table->string('scope', 4000)->nullable();
            $table->timestamp('expires');

            $table->primary('refresh_token');
            $table->foreign('client_id')->references('client_id')->on('oauth_clients');
            $table->foreign('user_id')->references('user_id')->on('oauth_users');
        });

        $this->schema->create('oauth_public_keys', function($table){
            $table->string('client_id', 80);
            $table->string('public_key', 4000);
            $table->string('private_key', 4000);
            $table->string('encryption_algorithm', 100)->default('RS256');

            $table->foreign('client_id')->references('client_id')->on('oauth_clients');
        });
    }

    /**
     * Undo the migration
     */
    public function down()
    {
        $this->schema->drop('oauth_scopes');
        $this->schema->drop('oauth_users');
        $this->schema->drop('oauth_clients');
        $this->schema->drop('oauth_access_tokens');
        $this->schema->drop('oauth_authorization_codes');
        $this->schema->drop('oauth_jwt');
        $this->schema->drop('oauth_refresh_tokens');
        $this->schema->drop('oauth_public_keys');
    }
}
