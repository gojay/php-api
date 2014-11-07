<?php
namespace App\Transformers;

use League\Fractal\TransformerAbstract,
	App\Model\Contact,
    App\Model\Note;

class ContactTransformer extends TransformerAbstract
{
	/**
     * List of resources to automatically include
     *
     * @var array
     */
    protected $availableIncludes = ['notes'];

    private $fields = [];

    public function setFields(array $fields)
    {
        $this->fields = $fields;

        if(!in_array('id', $this->fields)) {
            array_push($this->fields, 'id');
        }
    }

	public function Transform(Contact $contact)
	{
		$transform = [
			"id" 		=> (int) $contact->id,
			"firstname" => $contact->firstname,
			"lastname" 	=> $contact->lastname,
			"email" 	=> $contact->email,
			"phone" 	=> $contact->phone,
			"favorite" 	=> (boolean) $contact->favorite
		];

        return (!empty($this->fields)) ? array_intersect_key($transform, array_flip($this->fields)) : $transform ;
	}

	/**
     * Include Notes
     *
     * @return League\Fractal\Resource\Collection
     */
    public function includeNotes(Contact $contact, $includeParams)
    {
        $notes = $contact->notes();

        $noteTransformer = new NoteTransformer;

        if( $includeParams ) {
            // fields
            if(array_key_exists('fields', $includeParams)) {
                $noteTransformer->setFields($includeParams['fields']);
            }
            // order
            if(array_key_exists('order', $includeParams)) {
                $order = $includeParams['order'];
                $orderType = (count($order) == 1) ? 'asc' : $order[1];
                $notes->orderBy($order[0], $orderType);
            }
            // limit
            if(array_key_exists('limit', $includeParams)) {
                $notes->limit($includeParams['limit'][0]);
            }
        }

        return $this->collection($notes->get(), $noteTransformer);
    }
}