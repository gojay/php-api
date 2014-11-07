<?php
namespace App\Controller;

use App\Transformers\CreatorTransformer,
	App\Model\Creator,
	App\Model\CreatorMeta;

class SplashMobileController extends BaseController
{
	protected $creator_type = 'mobile';

	public function options()
	{
		$this->app->response->setStatus(200);
	}

	public function all()
	{
		$req = $this->app->request;

		$page  = $req->get('page') ? $req->get('page') : 1 ;
        $pPage = $req->get('per_page') ? $req->get('per_page') : 10 ;
        $skip  = ($page * $pPage) - $pPage;

        // Scope Filter, Field, Sort
        $mobiles = Creator::mobile()
                            ->sort($req->get('sort'))
                            ->take($pPage)->skip($skip)
                            ->get();

        // add include/nested table
        if( $include = $req->get('include') ) {
        	$this->addIncludeFractal($include);
        }

        // send response with collection JSON
        $this->respondWithCollection($mobiles, new CreatorTransformer);
	}

	public function get($id)
	{
		if(is_string($id) && $id === 'photos') {
			$photos = glob(UPLOAD_PATH ."/mobile_testimonial_*.{jpg,jpeg,png}", GLOB_BRACE);
			echo json_encode([
				'data' => array_map(function($name){
					return UPLOAD_URL . '/' . $name;
				}, array_map('basename', $photos))
			], JSON_PRETTY_PRINT);
			die;
		}

		$id = $this->sanitizeInt($id);

		$creator = Creator::findOrFail($id);

		$this->addIncludeFractal('meta');

		$this->respondWithItem($creator, new CreatorTransformer);
	}

	/*
	 * 	http://localhost:8080/api/v1/splash/mobiles
	 * {
		  	"title":"mobile 1",
		  	"description":"Lorem ipsum",
		  	"meta":[
		  		{
			    	"meta_key":"key array",
			    	"meta_value": {
				        "key1":"value1",
				        "key2":"value2",
				        "key3":"value3"
			      	}
			  	},
			  	{
				    "meta_key":"key string",
				    "meta_value":"example config2"
				},
			  	{
				    "meta_key":"key html",
				    "meta_value":"<h1>Lorem</h1><p>Lorem ipsum dolor sit amet</p>"
				}
			]
		}
	 */
	public function post()
	{
		$body = $this->app->request->getBody();
		$body['type'] = $this->creator_type;

		$errors = Creator::validate($body);

		if(!empty($errors)) {
			$this->throwValidationError("Invalid data", 0, $errors);
		}

		// remove notes
        if (isset($body['meta'])) {
            $meta = $body['meta'];
            unset($body['meta']);
        }

		$creator = Creator::create($body);
		if ( $creator ) {
            if (!empty($meta)) {
                $creatorMeta = array_map(function($_meta){
                	return new CreatorMeta($_meta);
                }, $meta);
                // save many notes
                $creator->meta()->saveMany($creatorMeta);
            }
        	// $this->respondCreateJSON('Creator has been added');
        	echo json_encode(['message' => 'Creator has been added', 'id' => $creator->id]);
        } else {
            $this->throwInternalError("Unable to save contact");
        }
	}

	/* 
	 * 	http://localhost:8080/api/v1/splash/mobiles/1
	 *  {
		  "title":"splash mobile 1",
		  "meta":[
		    {
		      "id":1,
		      "meta_value": {
		        "key1":"value1",
		        "key2":"value2"
		      }
		    },
		    {
		      "meta_key":"add key string",
		      "meta_value":"example value of key"
		    }
		  ]
		}
	 */
	public function update($id)
	{
		$id = $this->sanitizeInt($id);

		$body = $this->app->request->getBody();

		$errors = Creator::validate($body, 'update');

		if(!empty($errors)) {
			$this->throwValidationError("Invalid data", 0, $errors);
		}

		if (isset($body['meta'])) {
            $meta = $body['meta'];
            unset($body['meta']);
        }

		$creator = Creator::findOrFail($id);
		$updated = $creator->update($body);
		if ($updated) {
            // Process meta
            if (!empty($meta)) {
                foreach ($meta as $item) {
                    if (empty($item['id'])) {
                        // New note
                        $creator->meta()->save(new CreatorMeta($item));
                    } else {
                        // Existing note
                        $meta = $creator->meta()->findOrFail($item['id'])
                        					    ->update($item);
                    }
                }
            }

            $this->respondCreateJSON('Creator has been updated');

        } else {
        	$this->throwInternalError("Unable to save creator");
        }

	}

	public function delete($id)
	{
		$id = $this->sanitizeInt($id);

		$creator = Creator::findOrFail($id);

        $creator->delete();

        $this->app->halt(204);
	}
}