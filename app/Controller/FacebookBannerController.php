<?php
namespace App\Controller;

use App\Transformers\CreatorTransformer,
	App\Model\Creator,
	App\Model\CreatorMeta;

class FacebookBannerController extends BaseController 
{
	protected $creator_type = 'banner';

	public $templates = [
		'facebook' => [
			'/public/assets/facebook/fb-like1.png',
			'/public/assets/facebook/fb-like2.png',
			'/public/assets/facebook/fb-like3.png'
		],
		'badges' => [
			'/public/assets/badge/badge-1.png',
			'/public/assets/badge/badge-2.png',
			'/public/assets/badge/badge-3.png',
			'/public/assets/badge/badge-4.png'
		],
		'background' => [
			[
				'grass' => '/public/assets/banner/1-Prize-Background-Grass.jpg',
				'feris' => '/public/assets/banner/1-Prize-Background-Feris.jpg',
				'young' => '/public/assets/banner/1-Prize-Background-Young.jpg'
			],
			[
				'grass' => '/public/assets/banner/General-Background-Grass.jpg',
				'feris' => '/public/assets/banner/General-Background-Feris.jpg',
				'young' => '/public/assets/banner/General-Background-Young.jpg'
			],
			[
				'grass' => '/public/assets/banner/3-Prizes-Background-Grass.jpg',
				'feris' => '/public/assets/banner/3-Prizes-Background-Feris.jpg',
				'young' => '/public/assets/banner/3-Prizes-Background-Young.jpg'
			]
		]
	];

	public function options()
	{
		$this->app->response->setStatus(200);
	}

	public function all() 
	{
		$this->sendJSON(['message' => 'All']);
	}

	public function get($id) 
	{
		if(is_string($id) && $id === 'templates') {
			return $this->templates();
		}

		$this->sendJSON(['message' => 'GET :' . $id]);
	}

	public function templates()
	{
		array_walk_recursive($this->templates, function(&$item) {
			$item = BASE_URL . $item;
		});

		$this->sendJSON(['data' => $this->templates]);
	}

	public function post() 
	{
		$this->respondCreateJSON('Creator has been added');
	}

	public function update($id) 
	{
		$this->sendJSON(['message' => 'UPDATE :' . $id]);
	}

	public function delete($id) 
	{
		$this->respondDeleteJSON("Creator $id has been deleted");
	}

}
