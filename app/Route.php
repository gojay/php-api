<?php 
namespace App;

/**
 * Class App
 *
 * @method static \Slim\App get()
 * @method static \Slim\App post()
 * @method static \Slim\App put()
 * @method static \Slim\App delete()
 * @method static \Slim\App group()
 */
class Route
{
    public static function __callStatic($method, $args)
    {
        $instance = \App\Application::getInstance();

        switch (count($args))
        {
            case 0:
                return $instance->$method();

            case 1:
                return $instance->$method($args[0]);

            case 2:
                return $instance->$method($args[0], $args[1]);

            case 3:
                return $instance->$method($args[0], $args[1], $args[2]);

            case 4:
                return $instance->$method($args[0], $args[1], $args[2], $args[3]);

            default:
                return call_user_func_array(array($instance, $method), $args);
        }
    }
}