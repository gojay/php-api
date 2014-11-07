<?php
namespace App\Model;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Post extends Eloquent
{
	protected $table = 'Posts';

	protected $fillable = ['title', 'content'];
}