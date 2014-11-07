<?php
namespace App\Model;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Album extends Eloquent
{
	// protected $table = 'albums';
    
    protected $guarded = array();

	// Album __belongs_to__ Artist
    public function artist()
    {
        return $this->belongsTo('Artist');
    }

    // Album __belongs_to_many__ Listeners
    public function listeners()
    {
        return $this->belongsToMany('Listener');
    }
}