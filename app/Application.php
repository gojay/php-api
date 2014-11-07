<?php
namespace App;

use Slim\Slim;

class Application extends Slim
{
    protected $_oauthServer = null;

    public function setOauth( \OAuth2\Server $oauthServer )
    {
        $this->_oauthServer = $oauthServer;
    }

    public function getOauth()
    {
        return $this->_oauthServer;
    }
}
