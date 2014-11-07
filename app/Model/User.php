<?php
namespace App\Model;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class User extends Eloquent
{
	protected $table = 'oauth_users';

	protected $primaryKey = 'user_id';

	protected $visible = ['user_id', 'firstname', 'lastname', 'email'];
}