<?php

require_once 'project.php';
require 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(Project::web_root_directory() . '/templates');

$cache = (Project::debugging_mode() == false) ? Project::web_root_directory() ."/tmp/cache" : false;

$twig = new \Twig\Environment($loader, [
    'cache' => $cache,
]);

?>
