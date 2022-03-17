<?php

require_once 'Config.php';

$path = get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/..');
set_include_path($path);

function project_root_directory()
{
	static $path = NULL;

	if ($path === null) {
		$path = realpath(__DIR__ . '/../..');
	}
	return $path;
}

function project_web_root_directory()
{
	static $path = null;

	if ($path === null) {
   		$path = realpath(dirname(__FILE__) . '/..');
	}
	return $path;
}

function project_config_path()
{
	static $path = null;

	# Base directory outside of document root.
	if ($path === null) {
		$path = realpath(__DIR__ . '/../../config.ini');
	}
	return $path;
}

function project_debugging_mode()
{
   $config = new Config();

   return (bool) $config->settings['main']['debug'];
}

?>
