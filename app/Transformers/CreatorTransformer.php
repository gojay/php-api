<?php
namespace App\Transformers;

use League\Fractal\TransformerAbstract,
	App\Model\Creator,
    App\Model\CreatorMeta;

class CreatorTransformer extends TransformerAbstract
{
	/**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $availableIncludes = ['meta'];

    public function transform(Creator $creator)
    {
    	return [
    		'id' => (int) $creator->id,
            'title' => $creator->title,
    		'type' => $creator->type,
    		'screenshot' => $creator->screenshot,
    		'description' => html_entity_decode($creator->description, ENT_COMPAT, 'UTF-8')
    	];
    }

    public function includeMeta(Creator $creator)
    {
    	$meta = $creator->meta;

    	return $this->collection($meta, new creatorMetaTransformer);
    }
}