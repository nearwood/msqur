<?php
define('CONFIG_VERSION', "4");
define('DB_HOST', "localhost");
define('DB_USERNAME', "msqur");
define('DB_PASSWORD', "hNqsmjMpmCbnfNBj");
define('DB_NAME', "msqur");

define('DEBUG', TRUE);
define('DISABLE_MSQ_CACHE', TRUE);

error_reporting(E_ALL);

ini_set('display_errors', DEBUG ? 'On' : 'Off');
//ini_set('log_errors', DEBUG ? 'Off' : 'On');

//Default in case it's not set in php.ini
//MSQUR-1
date_default_timezone_set('UTC');
?>
