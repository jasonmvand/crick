<?php

error_reporting(E_ALL);
ini_set('display_errors', 'on');

define('Config\\DIR_APPLICATION', dirname(__FILE__) . '/');
require_once \Config\DIR_APPLICATION . 'config/config.php';
require_once \Config\DIR_LIBRARY . 'application.php';

date_default_timezone_set(\Config\TIMEZONE);

spl_autoload_register(function($classname){
	\Library\Application::import($classname);
});

//\Library\Gzip::start(); // Start Gzip output compression
$_CLIENT = new \Library\Client();

require_once $_CLIENT->request->path;
$_CLASSES = get_declared_classes();
$_CLASSNAME = array_pop($_CLASSES);
unset($_CLASSES);

$_APPLICATION = new $_CLASSNAME($_CLIENT);

exit();
/* EOF */