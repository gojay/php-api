<?php
namespace App\Model;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Note extends Eloquent 
{
	protected $fillable = array('body', 'contact_id');

	protected $visible = array('id', 'body', 'contact_id');

	public function subnotes()
	{
		return $this->hasMany('\App\Model\Subnote', 'note_id');
	}

	public function contact()
	{
		return $this->belongsTo('\App\Model\Contact', 'contact_id');
	}

    public function scopeSort($query, $sort)
    {
        if(empty($sort)) return;

        $sort = explode(',', $sort);
        $sort = array_map(
            function ($s) {
                $s = filter_var($s, FILTER_SANITIZE_STRING);
                return trim($s);
            },
            $sort
        );
        foreach ($sort as $expr) {
            $type = 'ASC';
            if ('-' == substr($expr, 0, 1)) {
                $expr = substr($expr, 1);
                $type = 'DESC';
            } 
            $query->orderBy($expr, $type);
        }
    }
    
    public static function validate($note = array(), $action = 'create')
    {
        $errors = array();

        $note = filter_var_array(
            $note,
            array(
                'id' => FILTER_SANITIZE_NUMBER_INT,
                'body' => FILTER_SANITIZE_STRING,
                'contact_id' => FILTER_SANITIZE_NUMBER_INT,
            ),
            false
        );
        
        if (isset($note['body']) && empty($note['body'])) {
            $errors[] = array(
                'field' => 'body',
                'message' => 'Note body cannot be empty'
            );
        }
        

        return $errors;
    }
}