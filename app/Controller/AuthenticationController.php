<?php
namespace App\Controller;

class AuthenticationController extends BaseController
{
	public function options()
	{
		$this->app->response->setStatus(200);
	}

	public function login()
	{
		$oauthServer = $this->app->getOauth();
		// Handle a request for an OAuth2.0 Access Token and send the response to the client
        $oauthServer->handleTokenRequest(\OAuth2\Request::createFromGlobals(), new \OAuth2\Response)->send();
    	die;
	}

	public function ping()
	{
		$this->app->response->setStatus(200);
	}

	public function me()
	{
		$oauthServer = $this->app->getOauth();
		$accessToken = $oauthServer->getAccessTokenData(\OAuth2\Request::createFromGlobals());

		if(empty($accessToken) || !$accessToken['user_id']) {
			$this->app->halt(401, json_encode([
				'error' => 'access_denied',
				'error_description' => 'An access token is required'
			]));
			die;
		}

		$user = \App\Model\User::findOrFail($accessToken['user_id']);
		echo $user->toJson();
	}
}