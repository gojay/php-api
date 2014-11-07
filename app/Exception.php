<?php

namespace App;

class Exception extends \Exception 
{ 
    CONST ERROR_FORBIDDEN = 403;
    CONST ERROR_METHOD_NOT_ALLOWED = 405;
    CONST ERROR_MEDIATYPE = 415;
    CONST ERROR_VALIDATION = 422;
}
