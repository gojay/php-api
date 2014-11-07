<?php
namespace App\Transformers;

use League\Fractal\TransformerAbstract,
	App\Model\Subnote;

class SubnoteTransformer extends TransformerAbstract
{
    private $fields = [];

    public function setFields(array $fields)
    {
        $this->fields = $fields;
        if(!in_array('id', $this->fields)) {
            array_push($this->fields, 'id');
        }
    }

	public function Transform(Subnote $sub)
	{
		$transform = [
			"id" 	  	 => (int) $sub->id,
			"meta_key" 	 => $sub->meta_key,
			"meta_value" => $sub->meta_value,
			"note_id" 	 => (int) $sub->note_id
		];
        return (!empty($this->fields)) ? array_intersect_key($transform, array_flip($this->fields)) : $transform ;
	}
}