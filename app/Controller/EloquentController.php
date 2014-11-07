<?php
namespace App\Controller;

use App\Model\Contact;

/**
* Eloquent
*/
class EloquentController extends BaseController
{
	public function index($param = null)
	{
		switch ($this->app->request->getMethod()) {
			case 'OPTIONS':
				return $this->app->response(200);
				break;
			case 'GET':
				return empty($param) ? $this->all() : $this->get($param);
				break;
			case 'POST':
				return $this->post();
				break;
			case 'PUT':
			case 'PATCH':
				return $this->update($param);
				break;
			case 'DELETE':
				return $this->delete($param);
				break;
		}

        $this->throwMethodError("Method {$this->app->request->getMethod()} doesn't exists", 1);
	}

    public function options()
    {
        echo $this->app->request->getMethod() . ':' . $this->app->request->getResourceUri();
    }

	public function all()
	{
		$req = $this->app->request;

		$filters = array();

		// Get and sanitize filters from the URL
        if ($rawfilters = $req->get()) {
            unset(
                $rawfilters['fields'],
                $rawfilters['sort'],
                $rawfilters['page'],
                $rawfilters['per_page'],
                $rawfilters['access_token']
            );
            foreach ($rawfilters as $key => $value) {
                $filters[$key] = filter_var(
                    $value,
                    FILTER_SANITIZE_STRING
                );
            }
        }

        // Scope Filter, Field, Sort
        // $contacts = Contact::with('notes')
							// ->leftJoin('notes', function($join) {
							// 	$join->on('contacts.id', '=', 'notes.contact_id');
							// })
        $contacts = Contact::filter($filters)
                            ->field($req->get('fields'))
                            ->sort($req->get('sort'));

        $page = $req->get('page') ? $req->get('page') : 1 ;
        $perPage = $req->get('per_page') ? $req->get('per_page') : 10 ;

        $skip = ($page * $perPage) - $perPage;
        $contacts->take($perPage)->skip($skip);

        echo $contacts->get()->toJson();          
	}

    public function get($id)
    {
        echo $this->app->request->getMethod() . ' SINGLE :' . $id . ':' . $this->app->request->getResourceUri();
    }

	public function post()
	{
		$body = $this->app->request()->getBody();

		$errors = $this->app->validateContact($body);

		if( !empty($errors) ) {
			throw new ValidationException("Invalid data", 0, $errors);
		}

		// $this->sendJson([
		// 	'action' => 'create',
		// 	'data'   => $body
		// ]);

        $this->respondCreateJSON('Contact has been add');
	}

	public function update($id)
	{
        echo $this->app->request->getMethod() . ':' . $id . ':' . $this->app->request->getResourceUri();
        die;

		$this->_checkParameterId( $id );

		$body = $this->app->request()->getBody();

		$errors = $this->app->validateContact($body, 'update');

		if( !empty($errors) ) {
			throw new ValidationException("Invalid data", 0, $errors);
		}

		echo json_encode([
			'action' => 'update',
			'id'	 => $id,
			'data'   => $body
		]);
	}

	public function delete($id)
	{
        echo $this->app->request->getMethod() . ':' . $id . ':' . $this->app->request->getResourceUri();
        die;

		$this->_checkParameterId( $id );

		echo json_encode([
			'action' => 'update',
			'id'	 => $id
		]);
	}

	public function test()
	{
		// Examples
        // =======================================

        /* 
         * create model and relation 
         *
        $contact = Contact::create([
            'firstname' => 'Dani',
            'lastname' => 'Gojay',
            'email' => 'dani.gojay@gmail.com'
        ]);
        $notes = [
            new Note(['body' => 'lorem ipsum']),
            new Note(['body' => 'lorem ipsum dolor']),
            new Note(['body' => 'lorem ipsum dolor sit amet']),
        ];
        $notes = $contact->notes()->saveMany($notes);

        $response = $contact->toArray();
        $response['notes'] = $notes;
        echo json_encode($response, JSON_PRETTY_PRINT);
        */

        /* 
         * Update 
         *
        $data = [
            'firstname' => 'camilia',
            'lastname'  => 'donati'
        ];
        $contact = Contact::find(1);
        // $updated = $contact->update($data);

        // $note = $contact->notes()->save(new Note([
        //     'body' => 'lorem ipsum'
        // ]));
        try {
            $note = $contact->notes()->findOrFail(7);
            $updated = $note->update([
                'body' => 'lorem ipsum dolor'
            ]);
        } catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            echo 'Not Found : ' . $e->getMessage();
            exit;
        }

        $res = [
            'updated' => $updated,
            'note' => $note->toArray(),
            'data' => $contact->toArray()
        ];
        var_dump($res);
         */

        /*
         * Save/Update Relation
         *
        $contact = Contact::find(1);
        $note = $contact->notes()->save(new Note([
            'body' => 'Lorem'
        ]));
        $note = $contact->notes()->find(7);
        $res = $note->update([
            'body' => 'lorems'
        ]);

        echo json_encode([
            'response' => $res,
            'note' => $note->toArray()
        ]);
        */

        /* 
         * Automatically delete relations 
        $contact = Contact::find(109);
        var_dump($contact->delete());
        */

        // $contacts = Contact::favorite(3)->get();
        // $contacts = Contact::has('notes')->get();
        // $contacts = Contact::with('notes')->get();
        // $contacts = Contact::all(['firstname', 'lastname']);

		/* 
         * Many to Many 
         */

        // create artist
        // $artist = new Artist;
        // $artist->name = 'Eve 6';
        // $artist->save();

        // save album associate artist
        // $album = new Album;
        // $album->name = 'Horrorscope';
        // $album->artist()->associate($artist);
        // $album->save();

        // Pivot many to many
        // $listener = new Listener;
        // $listener->name = 'Naruto Uzumaki';
        // $listener->save();
        // $listener->albums()->save($album);

        // echo json_encode([
        //     'artist'   => $artist->toJson(),
        //     'album'    => $album->toJson(),
        //     'listener' => $listener->toJson()
        // ], JSON_PRETTY_PRINT);

        // =======================================

        // Save album associate artist
        // $artist = Artist::find(1);
        // $album = new Album;
        // $album->name = 'Speed of Sound';
        // $album->artist()->associate($artist);
        // $album->save();
        // echo $album->toJson();

        // =======================================

        // Save listener refrence album
        // $listener = new Listener(['name' => 'Jiraiya']);
        // $listeners = [
        //     new Listener(['name' => 'Sakura']),
        //     new Listener(['name' => 'Kiba'])
        // ];

        // $album = Album::find(1);
        // $album->listeners()->save($listener);
        // $album->listeners()->saveMany($listeners);
        // echo $album->toJson();

        // =======================================

        // pivot get listeners by album
        // detach remove listeners
        // $album->listeners()->detach(5);
        // echo $album->listeners->toJson();

        // =======================================
	}
}