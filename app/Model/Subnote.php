<?php
namespace App\Model;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Subnote extends Eloquent
{
	protected $table = 'sub_notes';

	public function note()
	{
		return $this->belongsTo('App\Model\Note', 'note_id');
	}
}