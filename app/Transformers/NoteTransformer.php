<?php
namespace App\Transformers;

use League\Fractal\TransformerAbstract,
	App\Model\Note,
	App\Model\Subnote;

class NoteTransformer extends TransformerAbstract
{
	/**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $availableIncludes = ['subnotes'];

    private $fields = [];

    public function setFields(array $fields)
    {
        $this->fields = $fields;

        if(!in_array('id', $this->fields)) {
            array_push($this->fields, 'id');
        }
    }

	public function Transform(Note $note)
	{
		$transform = [
			"id" => (int) $note->id,
			"body" => $note->body,
			"contact_id" => (int) $note->contact_id
		];

        return (!empty($this->fields)) ? array_intersect_key($transform, array_flip($this->fields)) : $transform ;
	}

	/**
     * Include Subnote
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeSubnotes(Note $note, $includeParams)
    {
        $subnotes = $note->subnotes();

        $subNoteTransformer = new SubnoteTransformer;

        if( $includeParams ) {
            // fields
            if(array_key_exists('fields', $includeParams)) {
                $subNoteTransformer->setFields($includeParams['fields']);
            }
            // order
            if(array_key_exists('order', $includeParams)) {
                $order = $includeParams['order'];
                $orderType = (count($order) == 1) ? 'asc' : $order[1];
                $subnotes->orderBy($order[0], $orderType);
            }
            // limit
            if(array_key_exists('limit', $includeParams)) {
                $subnotes->limit($includeParams['limit'][0]);
            }
        }

        return $this->collection($subnotes->get(), $subNoteTransformer);
    }
}