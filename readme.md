
## DB Config
---

directory : share/config/default.php

```php
$config['db'] = array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => DBNAME,
    'username'  => DBUSERNAME,
    'password'  => DBPASSWORD,
    'prefix'    => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci'
);
```

---
## Install
---

__Library__

```sh
composer update
```

Edit __OAuth2\Request.php__

```php
public static function createFromGlobals()
{
    ...

    $contentType = $request->headers('CONTENT_TYPE', '');
    
    ...

    return $request;
}
```

Edit __OAuth2\Storage\Pdo.php__

```php
protected function checkPassword($user, $password)
{
    return password_verify($password, $user['password']);
}
```

Edit __OAuth2\Controller\ResourceController.php__

```php
public function getAccessTokenData(RequestInterface $request, ResponseInterface $response) {
    if ($token_param = $this->tokenType->getAccessTokenParameter($request, $response)) {
    
        ...
        
    } else {
        $response->setError(401, 'access_denied', 'An access token is required to request this resource');
    }
    
    ...
}
```

Edit __OAuth2\Controller\TokenController.php__

```php
public function grantAccessToken(RequestInterface $request, ResponseInterface $response)
{
    ...

    /**
     * Validate the client can use the requested grant type
     */
    ...

    /**
     * Validate the client can use this user
     */
    $clientDetails = $this->clientStorage->getClientDetails($clientId);
    if ($clientDetails['user_id'] !== $grantType->getUserId()) {
        $response->setError(400, 'unauthorized_client', 'This user is unauthorized for this client');

        return false;
    }
    
    ...
}
```

__DB Migration__

Using Schema Builder with CLI Migrations [Read more](http://thoughts.silentworks.co.uk/using-schema-builder-with-cli-migrations) with [phpmig](https://github.com/davedevelopment/phpmig)

```sh
cd bin
phpmig migrate
```

---
## Run Server
---

Required PHP >= 5.40

[PHP Built-in web server](http://php.net/manual/en/features.commandline.webserver.php)

```sh
php -S localhost:8080 -t public
```

---
## Run in Browser
---

[http://localhost:8080/api/v1/test}](http://localhost:8080/api/v1/test)
