<?php
require_once dirname(__FILE__) . '/../bootstrap.php';

use App\Route;

// $app->response()->header('Access-Control-Allow-Credentials', 'true'); //Allow JSON data to be consumed
// $app->response()->header('Access-Control-Allow-Origin', '*'); //Allow JSON data to be consumed
// $app->response()->header('Access-Control-Allow-Headers', 'X-Requested-With, X-authentication, X-client, Authorization');

Route::group('/api', function () {
	Route::group('/v1', function () {
	    Route::get('/test', 'App\Controller\TestController:index');
	    Route::get('/verification', 'App\Controller\TestController:verification');
	    Route::get('/jwt', 'App\Controller\TestController:jwt');
	    Route::map('/eloquent(/:param)', 'App\Controller\EloquentController:index')
	    	   ->via('OPTIONS', 'GET', 'POST', 'PUT', 'PATCH', 'DELETE');

	   	Route::group('/auth', function(){
		   	Route::options('/me', 'App\Controller\AuthenticationController:options');
		   	Route::get('/me', 'App\Controller\AuthenticationController:me');

		   	Route::options('/ping', 'App\Controller\AuthenticationController:options');
		   	Route::get('/ping', 'App\Controller\AuthenticationController:ping');
		   	Route::options('/login', 'App\Controller\AuthenticationController:options');
		   	Route::post('/login', 'App\Controller\AuthenticationController:login');
	   	});

	   	// Route::group('/pusher', function(){
		   // 	Route::get('/user', 'App\Controller\PusherController:options');
		   // 	Route::post('/auth/:channel', 'App\Controller\PusherController:auth');
		   // 	Route::post('/message', 'App\Controller\PusherController:message');
	   	// });

	   	Route::options('/upload', 'App\Controller\UploadController:options');
	   	Route::post('/upload', 'App\Controller\UploadController:upload');

	   	Route::options('/contacts', 'App\Controller\ContactController:options');
	   	Route::get('/contacts', 'App\Controller\ContactController:all');
	   	Route::get('/contacts/:id', 'App\Controller\ContactController:get');
	   	Route::post('/contacts', 'App\Controller\ContactController:post');
	   	Route::map('/contacts/:id', 'App\Controller\ContactController:update')->via('PUT', 'PATCH');
	   	Route::delete('/contacts/:id', 'App\Controller\ContactController:delete');

	   	Route::map('/contacts/:id/favorite', 'App\Controller\ContactController:favorite')->via('PUT', 'DELETE');

	   	Route::get('/contacts/:id/notes', 'App\Controller\NoteController:all');
	   	Route::get('/contacts/:id/notes/:note_id', 'App\Controller\NoteController:get');
	   	Route::post('/contacts/:id/notes', 'App\Controller\NoteController:post');
	   	Route::map('/contacts/:id/notes/:note_id', 'App\Controller\NoteController:update')->via('PUT', 'PATCH');
	   	Route::delete('/contacts/:id/notes/:note_id', 'App\Controller\NoteController:delete');

	   	Route::group('/facebook', function() {
	   		Route::options('/banners', 'App\Controller\FacebookBannerController:options');
	   		Route::options('/banners/:id', 'App\Controller\FacebookBannerController:options');
	   		Route::get('/banners', 'App\Controller\FacebookBannerController:all');
	   		Route::get('/banners/:id', 'App\Controller\FacebookBannerController:get');
	   		Route::post('/banners', 'App\Controller\FacebookBannerController:post');
	   		Route::map('/banners/:id', 'App\Controller\FacebookBannerController:update')->via('PUT', 'PATCH');
	   		Route::delete('/banners/:id', 'App\Controller\FacebookBannerController:delete');
	   	});

	   	Route::group('/splash', function(){
	   		Route::options('/mobiles', 'App\Controller\SplashMobileController:options');
	   		Route::options('/mobiles/:id', 'App\Controller\SplashMobileController:options');
	   		Route::get('/mobiles', 'App\Controller\SplashMobileController:all');
	   		Route::get('/mobiles/:id', 'App\Controller\SplashMobileController:get');
	   		Route::post('/mobiles', 'App\Controller\SplashMobileController:post');
	   		Route::map('/mobiles/:id', 'App\Controller\SplashMobileController:update')->via('PUT', 'PATCH');
	   		Route::delete('/mobiles/:id', 'App\Controller\SplashMobileController:delete');
	   	});
	});
});

$app->run();