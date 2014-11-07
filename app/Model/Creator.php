<?php
namespace App\Model;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Creator extends Eloquent 
{
	protected $fillable = ['title', 'type', 'screenshot', 'description'];

	public function meta()
	{
		return $this->hasMany('\App\Model\CreatorMeta', 'creator_id');
	}

	// scope
	public function scopeMobile($query)
	{
		$query->where('type', '=', 'mobile');
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

	public function delete()
    {
        $this->meta()->delete();
        return parent::delete();
    }

    public static function validate($creator = array(), $action = 'create')
    {
        $errors = array();
        
        if (!empty($creator['meta'])) {
            $meta = $creator['meta'];
            unset($creator['meta']);
        }

        $creator = filter_var_array(
            $creator,
            array(
                'title'			=> FILTER_SANITIZE_STRING,
                'type' 			=> FILTER_SANITIZE_STRING,
                'screenshot' 	=> FILTER_SANITIZE_STRING,
                'description' 	=> FILTER_SANITIZE_STRING, 
            ),
            false
        );
        
        switch ($action) {
            
            case 'update':
                if (isset($creator['title'])
                    && empty($creator['title'])) {
                    $errors['creator'][] = array(
                        'field' => 'title',
                        'message' => 'Title cannot be empty'
                    );
                }
                if (isset($creator['type'])
                    && empty($creator['type'])) {
                    $errors['creator'][] = array(
                        'field' => 'type',
                        'message' => 'Type cannot be empty'
                    );
                }
                break;
            
            case 'create':
            default:
                if (empty($creator['title'])) {
                    $errors['creator'][] = array(
                        'field' => 'title',
                        'message' => 'Title cannot be empty'
                    );
                }

                if (empty($creator['type'])) {
                    $errors['creator'][] = array(
                        'field' => 'type',
                        'message' => 'Type cannot be empty'
                    );
                }
                
                break;
        }
        

        if (!empty($meta) && is_array($meta)) {
            $metaCount = count($meta);
            for ($i = 0; $i < $metaCount; $i++) {
                
                $metaErrors = \App\Model\CreatorMeta::validate($meta[$i], $action);
                if (!empty($metaErrors)) {
                    $errors['meta'][] = $metaErrors;
                    unset($metaErrors);
                }

            }
        }

        return $errors;
    }
}