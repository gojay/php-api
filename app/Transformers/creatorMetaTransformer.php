<?php
namespace App\Transformers;

use League\Fractal\TransformerAbstract,
    App\Model\CreatorMeta;

class CreatorMetaTransformer extends TransformerAbstract
{
	public function transform(CreatorMeta $meta)
    {
    	return [
    		'id' => (int) $meta->id,
    		'meta_key' => $meta->meta_key,
    		'meta_value' => $meta->meta_value_serialized
    	];
    }
}