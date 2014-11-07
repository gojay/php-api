<?php

namespace App\Exception;

class MethodNotAllowedException extends \App\Exception
{
	private $data = array();
    
    public function __construct($message, $code = 0, $data = array())
    {
        parent::__construct($message, $code);
        
        $this->data = $data;
    }

    public function getData()
    {
        return $this->data;
    }
    
    public function getErrorCode()
    {
        return \App\Exception::ERROR_METHOD_NOT_ALLOWED;
    }
}
