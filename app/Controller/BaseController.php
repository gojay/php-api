<?php
namespace App\Controller;

use App\Exception\ForbiddenException;
use App\Exception\MethodNotAllowedException;
use App\Exception\ValidationException;

use League\Fractal,
    League\Fractal\TransformerAbstract,
    League\Fractal\Serializer\JsonApiSerializer;

abstract class BaseController
{
    protected $app;
    protected $fractal;
    protected $includeRelations;

    public function __construct()
    {
		$this->app = \App\Application::getInstance();
        $this->fractal = new Fractal\Manager();
        // $this->fractal->setRecursionLimit(2);
        // $this->fractal->setSerializer(new JsonApiSerializer);
    }

    public function addIncludeFractal( $relations )
    {
        $this->fractal->parseIncludes($relations);
    }

    public function getIncludeParams($include)
    {
        return $this->fractal->getIncludeParams($include);
    }

    public function getRequestedIncludes()
    {
        return $this->fractal->getRequestedIncludes();
    }

    public function respondWithCollection($collection, TransformerAbstract $transformer, $serializer = null)
    {
        $resource = new Fractal\Resource\Collection($collection, $transformer, $serializer);
        $this->respondJSON($resource);
    }

    public function respondWithItem($collection, TransformerAbstract $transformer, $serializer = null)
    {
        $resource = new Fractal\Resource\Item($collection, $transformer, $serializer);
        $this->respondJSON($resource);
    }

    public function respondJSON( $resource )
    {
        $res = $this->app->response;

        $body = $this->fractal->createData($resource)->toJson();

        $res->headers->set(
            'Content-Type',
            'application/json'
        );
        $res->setBody($body);
    }

    public function respondCreateJSON( $message )
    {
        $res = $this->app->response;

        $res->setStatus(201);
        $res->headers->set(
            'Content-Type',
            'application/json'
        );
        echo json_encode(['message' => $message], JSON_PRETTY_PRINT);
    }

    public function respondDeleteJSON( $message )
    {
        $res = $this->app->response;

        $res->setStatus(204);
        $res->headers->set(
            'Content-Type',
            'application/json'
        );
        echo json_encode(['message' => $message], JSON_PRETTY_PRINT);
    }

    public function sendJSON( $response )
    {
        $res = $this->app->response;

        $res->setStatus(200);
    	$res->headers->set(
            'Content-Type',
            'application/json'
        );
        echo json_encode($response, JSON_PRETTY_PRINT);
        die;
    }

    public function sendHtml( $data )
    {
    	$this->app->response->headers->set(
            'Content-Type',
            'text/html'
        );
        echo $data;
        die;
    }

    public function throwForbiddenError($message, $code = 0, $errors = [])
    {
        throw new \App\Exception\ForbiddenException($message, $code, $errors);
    }

    public function throwMethodError($message, $code = 0, $errors = [])
    {
        throw new \App\Exception\MethodNotAllowedException($message, $code, $errors);
    }

    public function throwMediaTypeError($message, $code = 0, $errors = [])
    {
        throw new \App\Exception\MediaTypeException($message, $code, $errors);
    }

    public function throwValidationError($message, $code = 0, $errors = [])
    {
        throw new \App\Exception\ValidationException($message, $code, $errors);
    }

    public function throwInternalError($message)
    {
        throw new \Exception($message);
    }

    protected function sanitizeInt($id)
    {
        if (empty($id)){
            throw new \App\Exception\ValidationException("Empty contact ID");
        } else {

            $id = filter_var(
                filter_var($id, FILTER_SANITIZE_NUMBER_INT),
                FILTER_VALIDATE_INT
            );

            if (false === $id) {
                throw new \App\Exception\ValidationException("Invalid contact ID");
            }

            return $id;

        }
    }

    protected function sanitizeParameters($fields)
    {
        if(empty($fields)) return;

        if( is_string($fields) ){
            $fields = explode(',', $fields);
        }
        
        return array_map(
            function ($field) {
                $field = filter_var(
                    $field,
                    FILTER_SANITIZE_STRING
                );
                return trim($field);
            },
            $fields
        );
    }
}