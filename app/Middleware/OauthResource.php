<?php
namespace App\Middleware;

class OauthResource extends \Slim\Middleware
{
	
	/**
     * @var array
     */
    protected $settings = array(
        'root'  => '/',
        'allowed_resources' => array(),
        'scopes' => array()
    );

    protected $scope = 'clientscope1';

    /**
     * Constructor
     *
     * @param   array  $config   Configuration and Login Details
     * @return  void
     */
    public function __construct(array $config = array())
    {
        if (!isset($this->app)) {
            $this->app = \Slim\Slim::getInstance();
        }
        $this->config = array_merge($this->settings, $config);
    }

    /**
     * Call
     *
     * This method will check the HTTP request headers for 
     * previous authentication. If the request has already authenticated,
     * the next middleware is called. Otherwise,
     * a 401 Authentication Required response is returned to the client.
     *
     * @return  void
     */
    public function call()
    {
        $req = $this->app->request();

        if( $req->isOptions() || $this->_isAllowed() ) return $this->next->call(); 

        if (preg_match(
            '|^' . $this->config['root'] . '.*|',
            $req->getResourceUri()
        )) {
        
            $oauthServer = $this->app->getOauth();
            $scopeRequired = $this->getScope(); // this resource required scope
            if (!$oauthServer->verifyResourceRequest(\OAuth2\Request::createFromGlobals(), new \OAuth2\Response, $scopeRequired)) {
                $oauthServer->getResponse()->send();
                die;
            }
        }
        
        $this->next->call();
    }

    private function getScope()
    {
        $resource = $this->app->request->getResourceUri();
        if(array_key_exists($resource, $this->config['scopes'])) {
            $this->scope = $this->config['scopes'][$resource];
        }
        return $this->scope;
    }

    /**
     * Check passed auth token
     *
     * @return boolean
     */
    private function _isAllowed()
    {
        $req = $this->app->request();
        $resource = strtoupper($req->getMethod()) . $req->getResourceUri() ;
        return in_array($resource, $this->config['allowed_resources']);
    }
}