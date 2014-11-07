<?php
namespace App\Model;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Artist extends Eloquent
{
	// protected $table = 'artists';
	
	protected $guarded = array();

    // Artist __has_many__ Album
    public function albums()
    {
        return $this->hasMany('Album');
    }
}