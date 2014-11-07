<?php
namespace App\Model;

use \Illuminate\Database\Eloquent\Model as Eloquent;

class CreatorMeta extends Eloquent 
{
	protected $table = 'creator_meta';

	protected $fillable = ['meta_key', 'meta_value'];

	public $timestamps = false;

	public function creator()
	{
		return $this->belongsTo('\App\Model\Creator', 'creator_id');
	}
	
	// Mutators
	public function setMetaValueAttribute($value)
	{
		$this->attributes['meta_value'] = is_array($value) ? serialize($value) : htmlentities($value, ENT_QUOTES, "utf-8");
	}

	// Accessors
	public function getMetaValueSerializedAttribute($value)
	{
		$value = @unserialize($this->meta_value);
    	return ($value === false) ? html_entity_decode($this->meta_value, ENT_QUOTES, "utf-8") : $value;
	}

    public static function validate($meta = array(), $action = 'create')
    {
        $errors = array();

        $meta = filter_var_array(
            $meta,
            array(
                // 'id'			=> FILTER_SANITIZE_NUMBER_INT,
                'meta_key'		=> FILTER_SANITIZE_STRING,
                'meta_value' 	=> FILTER_SANITIZE_STRING
            ),
            false
        );
        
        switch ($action) {
            
            case 'update':
                if (isset($meta['id'])
                    && empty($meta['id'])) {
                    $errors[] = array(
                        'field'   => 'id',
                        'message' => 'Meta ID cannot be empty'
                    );
                }
                if (isset($meta['meta_key'])
                    && empty($meta['meta_key'])) {
                    $errors[] = array(
                        'field'   => 'meta_key',
                        'message' => 'Meta key cannot be empty'
                    );
                }
                if (!isset($meta['meta_value'])) {
                    $errors[] = array(
                        'field'   => 'meta_value',
                        'message' => 'Meta value cannot be empty'
                    );
                }
                break;
            
            case 'create':
            default:
                if (empty($meta['meta_key'])) {
                    $errors[] = array(
                        'field'   => 'meta_key',
                        'message' => 'Meta key cannot be empty'
                    );
                }

                if (!isset($meta['meta_value'])) {
                    $errors[] = array(
                        'field'   => 'meta_value',
                        'message' => 'Meta value cannot be empty'
                    );
                }
                
                break;
        }

        return $errors;
    }
}