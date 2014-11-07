<?php
namespace App\Controller;

use App\Transformers\ContactTransformer;

class ContactController extends BaseController
{
	/*
	 * Fractal Transformers
	 * 
	 * http://localhost:8080/api/v1/contacts?
	 * 		include=notes.subnotes 		// notes will also be included with subnotes nested under it.
	 * 		advanced includes (example) :
	 * 		-----------------------------------------------------------
	 * 		include=notes:fields(id|body):order(id|desc):limit(5),notes.subnotes:fields(name_key|meta_value):order(id|desc):limit(3)
	 * 		contacts -> relation with notes
	 * 			fields  : ['id','body']
	 *			orderBy : ['id', 'desc']
	 *			limit 	: 5
	 *			notes -> relation with subnotes
	 * 				fields  : ['name_key', 'meta_value']
	 * 				orderBy : ['id', 'desc']
	 * 				limit 	: 3
	 * 		-----------------------------------------------------------
	 * 		&fileds={fields}				// csv
	 * 		&firstname={match_firstname}
	 * 		&lastname={match_lastname}
	 * 		&lastname={match_lastname}
	 * 		&email={match_email}
	 * 		&q={search}
	 * 		&sort={column} // (-)DESC
	 */
	public function all()
	{
		$req = $this->app->request;

		$page  = $req->get('page') ? $req->get('page') : 1 ;
        $pPage = $req->get('per_page') ? $req->get('per_page') : 10 ;
        $skip  = ($page * $pPage) - $pPage;

        // Scope Filter, Field, Sort
        $contacts = \App\Model\Contact::filter($req->get())
                            ->sort($req->get('sort'))
                            ->take($pPage)->skip($skip)
                            ->get();

        // add include/nested table
        if( $include = $req->get('include') ) {
        	$this->addIncludeFractal($include);
        }

        // send response with collection JSON
        $contactTransformer = new ContactTransformer;
        if( $fields = $req->get('fields') ) {
        	$fields = $this->sanitizeParameters($fields);
        	$contactTransformer->setFields($fields);
        }
        $this->respondWithCollection($contacts, $contactTransformer);
	}

	/*
	 * http://localhost:8080/api/v1/contacts/1?include=notes,notes.subnotes
	 */
	public function get($id)
	{
		$id = $this->sanitizeInt($id);

    	$contact = \App\Model\Contact::findOrFail($id);
        // add include/nested table
        if( $include = $this->app->request->get('include') ) {
        	$this->addIncludeFractal($include);
        }

        $this->respondWithItem($contact, new ContactTransformer);   
	}

	public function post()
	{
		$body = $this->app->request->getBody();

		$errors = \App\Model\Contact::validate($body);

		// throw validation error
		if( !empty($errors) ) {
			$this->throwValidationError("Invalid data", 0, $errors);
		}

		// remove notes
        if (isset($body['notes'])) {
            $notes = $body['notes'];
            unset($body['notes']);
        }

        // Create new contact
        $contact = \App\Model\Contact::create($body);
        if ( $contact ) {
            if (!empty($notes)) {
                $contactNotes = array();
                foreach ($notes as $note) {
                    $contactNotes[] = new \App\Model\Note($note);
                }
                // save many notes
                $notes = $contact->notes()->saveMany($contactNotes);
            }
        	$this->respondCreateJSON('Contact has been added');
        } else {
            $this->throwInternalError("Unable to save contact");
        }
	}

	public function update($id)
	{
		$id = $this->sanitizeInt($id);

		$body = $this->app->request()->getBody();

		$errors = \App\Model\Contact::validate($body, 'update');

		if( !empty($errors) ) {
			$this->throwValidationError("Invalid data", 0, $errors);
		}

		if (isset($body['notes'])) {
            $notes = $body['notes'];
            unset($body['notes']);
        }

        $contact = \App\Model\Contact::findOrFail($id);
        $updated = $contact->update($body);
        if ($updated) {

            // Process notes
            if (!empty($notes)) {
                foreach ($notes as $item) {
                    $_notes = [];
                    if (empty($item['id'])) {
                        // New note
                        $_notes[] = $contact->notes()->save(new \App\Model\Note($item));
                    } else {
                        // Existing note
                        $note = $contact->notes->find($item['id']);
                        if( $note ) $note->update($item);
                    }
                }
            }

            $this->respondCreateJSON('Contact has been updated');

        } else {
        	$this->throwInternalError("Unable to save contact");
        }
	}

	public function delete($id)
	{
		$id = $this->sanitizeInt($id);

		$contact = \App\Model\Contact::findOrFail($id);

        $contact->delete();

        $this->app->halt(204);
	}

	public function favorite($id)
	{
		$req = $this->app->request;

		$contact = \App\Model\Contact::findOrFail($id);

		switch ($this->app->request->getMethod()) {
			case 'PUT':
				// set true favorite contact
				$contact->favorite = true;
				break;
			case 'DELETE':
				// set false favorite contact
				$contact->favorite = false;
				break;
		}

		if($contact->save()) {
			$this->respondCreateJSON('Contact has been updated');
		}
		$this->throwInternalError('Unable to save contact');
	}

	public function options()
	{
		$this->app->response->setStatus(200);
	}
}