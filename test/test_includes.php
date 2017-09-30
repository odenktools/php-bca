<?php

$dir = dirname(__FILE__);
$config_path = $dir.'/config.php';
if (file_exists($config_path) === true) {
    require_once $config_path;
} else {
    define('BCA_CLIENT_ID', getenv('BCA_CLIENT_ID'));
    define('BCA_CLIENT_SECRET', getenv('BCA_CLIENT_SECRET'));
    define('BCA_APIKEY', getenv('BCA_APIKEY'));
    define('BCA_SECRETKEY', getenv('BCA_SECRETKEY'));
    define('BCA_HOST', 'https://sandbox.bca.co.id:443');
}

require_once $dir.'/../lib/Bca.php';
