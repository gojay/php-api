<?php
namespace App\Controller;

class NoteController extends BaseController
{
	public function options()
	{
		$this->app->response->setStatus(200);
	}

	/*
	 * Fractal Transformers
	 * 
	 * http://localhost:8080/api/v1/contacts/{:id}/notes?
	 * 		include=notes.subnotes 		// notes will also be included with subnotes nested under it.
	 * 		advanced includes (example) :
	 * 		-----------------------------------------------------------
	 * 		include=subnotes:fields(name_key|meta_value):order(id|desc):limit(3)
	 *		notes -> relation with subnotes
	 * 			fields  : ['name_key', 'meta_value']
	 * 			orderBy : ['id', 'desc']
	 * 			limit 	: 3
	 * 		-----------------------------------------------------------
	 * 		&fields={fields}				// csv
	 * 		&sort={column} // (-)DESC
	 */
	public function all()
	{
		$req = $this->app->request;

		$page  = $req->get('page') ? $req->get('page') : 1 ;
        $pPage = $req->get('per_page') ? $req->get('per_page') : 10 ;
        $skip  = ($page * $pPage) - $pPage;

        // Scope Filter, Field, Sort
        $notes = \App\Model\Note::sort($req->get('sort'))
                            ->take($pPage)->skip($skip)
                            ->get();

        // add include/nested table
        if( $include = $req->get('include') ) {
        	$this->addIncludeFractal($include);
        }

        // send response with collection JSON
        $noteTransformer = new \App\Transformers\NoteTransformer;
        if( $fields = $req->get('fields') ) {
        	$fields = $this->sanitizeParameters($fields);
        	$noteTransformer->setFields($fields);
        }
        $this->respondWithCollection($notes, $noteTransformer);
	}

	/*
	 * http://localhost:8080/api/v1/contacts/{:id}/notes?include=subnotes
	 */
	public function get($id, $note_id)
	{
		$id = $this->sanitizeInt($id);
		$note_id = $this->sanitizeInt($note_id);

		$note = \App\Model\Contact::findOrFail($id)
									->notes()
									->findOrFail($note_id);

        // add include/nested table
        if( $include = $this->app->request->get('include') ) {
        	$this->addIncludeFractal($include);
        }

        $this->respondWithItem($note, new \App\Transformers\NoteTransformer);
			
	}

	public function post($id)
	{
		$contact = \App\Model\Contact::findOrFail($id);

		$body = $this->app->request->getBody();

		$errors = \App\Model\Note::validate($body);

		if($errors) {
			$this->throwValidationError("Invalid data", 0, $errors);
		}

		$note = $contact->notes()->save(new \App\Model\Note($body));
		if($note) {
			$this->respondCreateJSON('Note has been saved');
		} else {
            $this->throwInternalError("Unable to save note");
        }
	}

	public function update($id, $note_id)
	{
		$note = \App\Model\Contact::findOrFail($id)
									->notes()
									->findOrFail($note_id);

		$body = $this->app->request->getBody();

		$errors = \App\Model\Note::validate($body, 'update');

		if($errors) {
			$this->throwValidationError("Invalid data", 0, $errors);
		}

		$updated = $note->update($body);
		if($updated) {
			$this->respondCreateJSON('Note has been updated');
		} else {
            $this->throwInternalError("Unable to save note");
        }
	}

	public function delete($id, $note_id)
	{
		$note = \App\Model\Contact::findOrFail($id)
									->notes()
									->findOrFail($note_id);

		if($note->delete()) {
			$this->app->halt(204);
		} else {
            $this->throwInternalError("Unable to save note");
        }
	}
}