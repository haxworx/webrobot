<?php

require_once 'common.php';
require 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(project_root_directory() . '/templates');

$cache = (project_debugging_mode() == false) ? "tmp/cache" : false;

$twig = new \Twig\Environment($loader, [
	'cache' => $cache,
]);

?>
