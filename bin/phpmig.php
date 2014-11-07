<?php
/** 
 * Using Schema Builder with CLI Migrations
 *
 * https://github.com/davedevelopment/phpmig
 * http://thoughts.silentworks.co.uk/using-schema-builder-with-cli-migrations/
 *
 * phpmig generate [ClassName]
 * phpmig migrate
 * phpmig rollback
 * 		  down [key]
 */
require_once '../vendor/autoload.php';

use \Pimple,
	\Phpmig\Adapter,
	\Illuminate\Database\Capsule\Manager as Capsule;

// Init application mode
if (empty($_ENV['SLIM_MODE'])) {
    $_ENV['SLIM_MODE'] = (getenv('SLIM_MODE'))
        ? getenv('SLIM_MODE') : 'development';
}

$configFile = dirname(__FILE__) . '/../share/config/'
    . $_ENV['SLIM_MODE'] . '.php';

if (is_readable($configFile)) {
    require_once $configFile;
} else {
    require_once dirname(__FILE__) . '/../share/config/default.php';
}

$container = new Pimple();

$container['config'] = $config['db'];

$container['db'] = $container->share(function($c) use ($config) {
    $dbh = new PDO($config['db']['dsn'], $config['db']['username'], $config['db']['password']);
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $dbh;
});

$container['schema'] = $container->share(function($c) {
    /* Bootstrap Eloquent */
    $capsule = new Capsule;
    $capsule->addConnection($c['config']);
    $capsule->setAsGlobal();
    /* Bootstrap end */

    return Capsule::schema();
});

$container['phpmig.adapter'] = $container->share(function() use ($container) {
    return new Adapter\PDO\Sql($container['db'], 'migrations');
});

// replace this with a better Phpmig\Adapter\AdapterInterface
$container['phpmig.adapter'] = new Adapter\File\Flat(__DIR__ . DIRECTORY_SEPARATOR . 'migrations/.migrations.log');

$container['phpmig.migrations_path'] = __DIR__ . DIRECTORY_SEPARATOR . 'migrations';

// You can also provide an array of migration files
// $container['phpmig.migrations'] = array_merge(
//     glob('migrations_1/*.php'),
//     glob('migrations_2/*.php')
// );

return $container;