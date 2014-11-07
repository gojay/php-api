<?php
define('BASE_URL', 'http://' . $_SERVER['SERVER_NAME'] . '/banner-api');
define('BASE_PATH', dirname(__FILE__));
define('UPLOAD_URL', BASE_URL . '/banner-api/public/images');
define('UPLOAD_PATH', BASE_PATH . '/public/images');

require_once dirname(__FILE__) . '/vendor/autoload.php';

use Slim\Slim;
use App\Application;
use App\Middleware\OauthResource;
use Flynsarmy\SlimMonolog\Log\MonologWriter;
use OAuth2\OAuth2\Autoloader;
use App\OAuth2\Scope;

use App\Exception;
use App\Exception\ValidationException;

use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

// Init application mode
if( $_SERVER['REMOTE_ADDR'] == '127.0.0.1' ) {
    error_reporting(E_ALL); 
    ini_set('display_errors','on');
    $_ENV['SLIM_MODE'] = 'development';
} else {
    $_ENV['SLIM_MODE'] = 'production';
}

// Init and load configuration
$config = array();

$configFile = dirname(__FILE__) . '/share/config/' . $_ENV['SLIM_MODE'] . '.php';

if (is_readable($configFile)) {
    require_once $configFile;
} else {
    require_once dirname(__FILE__) . '/share/config/default.php';
}

// Create Application
// ======================================================
$app = new Application($config['app']);

// Only invoked if mode is "production"
$app->configureMode('production', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'log.level' => \Slim\Log::WARN,
        'debug' => false
    ));
});

// Only invoked if mode is "development"
$app->configureMode('development', function () use ($app) {
    $app->config(array(
        'log.enable' => true,
        'log.level' => \Slim\Log::DEBUG,
        'debug' => false
    ));
});

// Get log writer
$log = $app->getLog();

// Init database Eloquent
// http://www.slimframework.com/news/slim-and-laravel-eloquent-orm
// ======================================================
try {

    $capsule = new Capsule;
    $capsule->addConnection($config['db']);
    // Set the event dispatcher used by Eloquent models... (optional)
    $capsule->setEventDispatcher(new Dispatcher(new Container));
    // Make this Capsule instance available globally via static methods... (optional)
    $capsule->setAsGlobal();
    // Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
    $capsule->bootEloquent();

} catch (\PDOException $e) {
    $log->error($e->getMessage());
}

// Oauth2
// ======================================================

OAuth2\Autoloader::register();
// $dsn is the Data Source Name for your database
$connection = [
    'dsn'       => $config['db']['dsn'], 
    'username'  => $config['db']['username'], 
    'password'  => $config['db']['password']
];

$pdoStorage = new OAuth2\Storage\Pdo($connection);
// Pass a storage object or array of storage objects to the OAuth2 server class
$oauthServer = new OAuth2\Server($pdoStorage, [
    // 'access_lifetime' => 60 // 1 minute, for testing
]);

/**
 * Scope
 *
 * Add the "Scope" used implements OAuth2\Storage\ScopeInterface
 *
 * http://bshaffer.github.io/oauth2-server-php-docs/overview/scope/
 */
$oauthServer->setScopeUtil(new OAuth2\Scope(new Scope($connection)));

/**
 * Client Credentials grant type
 *
 * Add the "Client Credentials" grant type (it is the simplest of the grant types)
 *
 * http://bshaffer.github.io/oauth2-server-php-docs/grant-types/client-credentials/
 */
$oauthServer->addGrantType(new OAuth2\GrantType\ClientCredentials($pdoStorage));

/**
 * User Credentials grant type
 *
 * The User Credentials grant type (a.k.a. Resource Owner Password Credentials) 
 * is used when the user has a trusted relationship with the client, and so can supply credentials directly.
 *
 * http://bshaffer.github.io/oauth2-server-php-docs/grant-types/user-credentials/
 */
$oauthServer->addGrantType(new OAuth2\GrantType\UserCredentials($pdoStorage));

/**
 * Refresh Token grant type
 *
 * Add the "Refresh Token" grant type is used to obtain additional access tokens 
 * in order to prolong the client's authorization of a user's resources.
 *
 * http://bshaffer.github.io/oauth2-server-php-docs/grant-types/refresh-token/
 *
 * the refresh token grant request will have a "refresh_token" field
 * with a new refresh token on each request
 */
$oauthServer->addGrantType(new OAuth2\GrantType\RefreshToken($pdoStorage, array(
    'always_issue_new_refresh_token' => true
)));

/**
 * JWT Bearer grant type
 *
 * The JWT Bearer grant type is used when the client wants to receive access tokens 
 * without transmitting sensitive information such as the client secret. 
 * This can also be used with trusted clients to gain access to user resources without user authorization.
 *
 * http://bshaffer.github.io/oauth2-server-php-docs/grant-types/jwt-bearer/
 */
$grantType = new OAuth2\GrantType\JwtBearer($pdoStorage, "http://localhost:8080");
// add the grant type to your OAuth server
$oauthServer->addGrantType($grantType);

/**
 * Crypto Token
 *
 * Crypto tokens provide a way to create and validate access tokens without requiring a central storage such as a database.
 * This decreases the latency of the OAuth2 service when validating Access Tokens.
 *
 * http://bshaffer.github.io/oauth2-server-php-docs/overview/crypto-tokens/
 */
// Make the "access_token" storage use Crypto Tokens instead of a database

// memory signature
// $publicKey  = file_get_contents(__DIR__ . '/SSH/pubkey.pem');
// $privateKey = file_get_contents(__DIR__ . '/SSH/privkey.pem');
// $keyStorage = new OAuth2\Storage\Memory(array('keys' => array(
//     'public_key'  => $publicKey,
//     'private_key' => $privateKey,
//     // 'encryption_algorithm'  => 'HS256', // "RS256" is the default
// )));
// $cryptoStorage = new OAuth2\Storage\CryptoToken($keyStorage);
$cryptoStorage = new OAuth2\Storage\CryptoToken($pdoStorage);
$oauthServer->addStorage($cryptoStorage, 'access_token');
// make the "token" response type a CryptoToken
$cryptoResponseType = new OAuth2\ResponseType\CryptoToken($pdoStorage);
$oauthServer->addResponseType($cryptoResponseType);

$app->setOauth($oauthServer);

// Middleware
// ======================================================

// Cache Middleware (inner)
// $app->add(new App\Middleware\Cache('/api/v1'));

// Parses JSON body
$app->add(new \Slim\Middleware\ContentTypes());

// Manage Rate Limit
// $app->add(new App\Middleware\RateLimit('/api/v1'));

// JSON Middleware
// $app->add(new App\Middleware\JSON('/api/v1'));

// Oauth Middleware
/*
$app->add(new App\Middleware\OauthResource(array(
    'root' => '/api/v1',
    'allowed_resources' => [
        'GET/api/v1/test',
        'GET/api/v1/verification',
        'GET/api/v1/jwt',
        'GET/api/v1/eloquent',
        'POST/api/v1/auth/login'
    ],
    'scopes' => [
        // '/api/v1/contacts' => 'clientscope2'
    ]
)));
*/
// Exception Handler
// ======================================================

/**
 * JSON friendly errors
 * NOTE: debug must be false
 * or default error template will be printed
 */
$app->error(function (\Exception $e) use ($app, $log) {

    $mediaType = $app->request->getMediaType();

    $isAPI = (bool) preg_match('|^/api/v.*$|', $app->request->getResourceUri());

    // Standard exception data
    $error = array(
        'code' => $e->getCode(),
        'message' => $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine(),
    );

    // Graceful error data for production mode
    if (!in_array(
            get_class($e),
            array('App\\Exception', 'App\\Exception\ValidationException')
        ) && 'production' === $app->config('mode')) {
        $error['message'] = 'There was an internal error';
        unset($error['file'], $error['line']);
    }

    // Custom error code (e.g. Validations)
    if (method_exists($e, 'getErrorCode')) {
        $app->response->setStatus($e->getErrorCode());
    }

    // Custom error data (e.g. Validations)
    if (method_exists($e, 'getData')) {
        $errors = $e->getData();
    }

    if (!empty($errors)) {
        $error['errors'] = $errors;
    }

    $log->error($e->getMessage());
       
    if ('application/json' === $mediaType || true === $isAPI) {
        $app->response->headers->set(
            'Content-Type',
            'application/json'
        );
        echo json_encode([
            'errors' => $error
        ], JSON_PRETTY_PRINT);
    } else {
        $app->response->headers->set(
            'Content-Type',
            'text/html'
        );
        echo '<html>
        <head><title>Error</title></head>
        <body><h1>Error: ' . $error['code'] . '</h1><p>'
        . $error['message']
        .'</p></body></html>';
    }
});

/**
 * Custom 404 error
 */
$app->notFound(function () use ($app) {

    $mediaType = $app->request->getMediaType();

    $isAPI = (bool) preg_match('|^/api/v.*$|', $app->request->getResourceUri());

    if ('application/json' === $mediaType || true === $isAPI) {

        $app->response->headers->set(
            'Content-Type',
            'application/json'
        );

        echo json_encode(
            array(
                'code' => 404,
                'message' => 'Not found'
            ),
            JSON_PRETTY_PRINT
        );

    } else {
        $app->response->headers->set(
            'Content-Type',
            'text/html'
        );
        echo '<html>
        <head><title>404 Page Not Found</title></head>
        <body><h1>404 Page Not Found</h1><p>The page you are
        looking for could not be found. </p></body></html>';
    }
});
