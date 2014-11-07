<?php
/**
 * Common configuration
 */

// $config['db'] = array(
//     'driver' => 'sqlite',
//     'dbname' => $_ENV['SLIM_MODE'] . '.sqlite',
//     'dbpath' => realpath(__DIR__ . '/../db')
// );

$config['db'] = array(
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'api',
    'username'  => 'root',
    'password'  => '',
    'prefix'    => '',
    'charset'   => "utf8",
    'collation' => "utf8_unicode_ci"
);

if( $config['db']['driver'] == 'sqlite' ){
    $dsn = sprintf(
        '%s:%s/%s',
        $config['db']['driver'],
        $config['db']['dbpath'],
        $config['db']['database']
    );
}
elseif( $config['db']['driver'] == 'mysql' ) {
    // $dsn = 'mysql:host=localhost;dbname=development.rest'
    $dsn =  sprintf(
        '%s:host=%s;dbname=%s',
        $config['db']['driver'],
        $config['db']['host'],
        $config['db']['database']
    );
}

$config['db']['dsn'] = $dsn;

$config['app']['mode'] = $_ENV['SLIM_MODE'];

// Cache TTL in seconds
$config['app']['cache.ttl'] = 60;

// Max requests per hour
$config['app']['rate.limit'] = 1000;

$config['app']['log.writer'] = new \Flynsarmy\SlimMonolog\Log\MonologWriter(array(
    'handlers' => array(
        new \Monolog\Handler\StreamHandler(
            realpath(__DIR__ . '/../logs')
                .'/'.$_ENV['SLIM_MODE'] . '_' .date('Y-m-d').'.log'
        ),
    ),
));
