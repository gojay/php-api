<?php
namespace App\Model;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Listener extends ELoquent
{
	// protected $table = 'listeners';

	protected $guarded = array();

    // Listener __belongs_to_many__ Album
    public function albums()
    {
        return $this->belongsToMany('Album');
    }
} 