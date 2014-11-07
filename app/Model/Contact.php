<?php
namespace App\Model;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class Contact extends Eloquent 
{	
	protected $fillable = array('firstname', 'lastname', 'email', 'phone', 'favorite');

	protected $hidden = array('created_at', 'updated_at');

	// mutators
	public function setFirstnameAttribute($value)
	{
		$this->attributes['firstname'] = ucfirst(strtolower($value));
	}

	public function setLastnameAttribute($value)
	{
		$this->attributes['lastname'] = ucfirst(strtolower($value));
	}

	// Accessors
	public function getFullNameAttribute($value)
	{
    	return $this->firstname . " " . $this->lastname;
	}

	public function notes()
	{
		return $this->hasMany('\App\Model\Note', 'contact_id');
	}

	// scope
	public function scopeFavorite($query, $take = 5)
	{
		$query->where('favorite', 1)->take($take);
	}

	public function scopeFilter($query, $_rawfilters)
	{
		if(empty($_rawfilters)) return;

		$filters = array();

		$rawfilters = array_intersect_key($_rawfilters, array_flip([
			'q',
			'firstname',
			'lastname',
			'email'
			// diff
			//----------------
			// 'relation', 
			// 'fields', 
			// 'sort', 
			// 'page', 
			// 'per_page', 
			// 'access_token'
		]));
		// Get and sanitize filters from the URL
		$filters = array_map(function($value){
			return filter_var(
                $value,
                FILTER_SANITIZE_STRING
            );
		}, $rawfilters);

		foreach ($filters as $key => $value) {
	        if( $key === 'q' ) {
	        	if( array_key_exists('firstname', $filters)) {
	        		$query->where('email', 'LIKE', "%{$value}%");
	        	} else {
		            $query->where('firstname', 'LIKE', "%{$value}%")
		                  ->orWhere('email', 'LIKE', "%{$value}%");
	        	}
	        } else {
	            $query->where($key, $value);
	        }
	    }
	}

	/**
	 * Manage sort options
     * sort=firstname => ORDER BY firstname ASC
     * sort=-firstname => ORDER BY firstname DESC
     * sort=-firstname,email =>
     * ORDER BY firstname DESC, email ASC
     */
	public function scopeField($query, $fields)
	{
		if(empty($fields)) return;

		$fields = explode(',', $fields);
        $fields = array_map(
            function ($field) {
                $field = filter_var(
                    $field,
                    FILTER_SANITIZE_STRING
                );
                return trim($field);
            },
            $fields
        );

        if(!in_array('id', $fields)) {
        	array_push($fields, 'id');
        }

        $query->select($fields);
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

 	// http://stackoverflow.com/questions/14174070/automatically-deleting-related-rows-in-laravel-eloquent-orm
	public function delete()
    {
        // delete all related photos 
        $this->notes()->delete();
        // as suggested by Dirk in comment,
        // it's an uglier alternative, but faster
        // Photo::where("user_id", $this->id)->delete()

        // delete the user
        return parent::delete();
    }

    public static function validate($contact = array(), $action = 'create')
    {
        $errors = array();
        
        if (!empty($contact['notes'])) {
            $notes = $contact['notes'];
            unset($contact['notes']);
        }

        $contact = filter_var_array(
            $contact,
            array(
                'id' 		=> FILTER_SANITIZE_NUMBER_INT,
                'firstname' => FILTER_SANITIZE_STRING,
                'lastname' 	=> FILTER_SANITIZE_STRING,
                'email' 	=> FILTER_SANITIZE_EMAIL,
                'phone' 	=> FILTER_SANITIZE_STRING, 
            ),
            false
        );
        
        switch ($action) {
            
            case 'update':
                // if (empty($contact['id'])) {
                //     $errors['contact'][] = array(
                //         'field' => 'id',
                //         'message' => 'ID cannot be empty on update'
                //     );
                //     break;
                // }
                if (isset($contact['firstname'])
                    && empty($contact['firstname'])) {
                    $errors['contact'][] = array(
                        'field' => 'firstname',
                        'message' => 'First name cannot be empty'
                    );
                }
                if (isset($contact['email'])) {
                    if (empty($contact['email'])) {
                        $errors['contact'][] = array(
                            'field' => 'email',
                            'message' => 'Email address cannot be empty'
                        );
                        break;
                    }
            
                    if (false === filter_var(
                        $contact['email'],
                        FILTER_VALIDATE_EMAIL
                    )) {
                        $errors['contact'][] = array(
                            'field' => 'email',
                            'message' => 'Email address is invalid'
                        );
                        break;
                    }
            
                    // Test for unique email
                    $results = self::where('email', $contact['email'])->count();
                    if ($results > 0) {
                        $errors['contact'][] = array(
                            'field' => 'email',
                            'message' => 'Email address already exists'
                        );
                    }
                }
                break;
            
            case 'create':
            default:
                if (empty($contact['firstname'])) {
                    $errors['contact'][] = array(
                        'field' => 'firstname',
                        'message' => 'First name cannot be empty'
                    );
                }
                if (empty($contact['email'])) {
                    $errors['contact'][] = array(
                        'field' => 'email',
                        'message' => 'Email address cannot be empty'
                    );
                } elseif (false === filter_var(
                    $contact['email'],
                    FILTER_VALIDATE_EMAIL
                )) {
                        $errors['contact'][] = array(
                            'field' => 'email',
                            'message' => 'Email address is invalid'
                        );
                } else {
                
                    // Test for unique email
                    $results = \App\Model\Contact::where('email', $contact['email'])->count();
                    if ($results > 0) {
                        $errors['contact'][] = array(
                            'field' => 'email',
                            'message' => 'Email address already exists'
                        );
                    }
                }
                
                break;
        }
        

        if (!empty($notes) && is_array($notes)) {
            $noteCount = count($notes);
            for ($i = 0; $i < $noteCount; $i++) {
                
                $noteErrors = \App\Model\Note::validate($notes[$i], $action);
                if (!empty($noteErrors)) {
                    $errors['notes'][] = $noteErrors;
                    unset($noteErrors);
                }

            }
        }

        return $errors;
    }
}
