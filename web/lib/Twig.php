<?php

require_once 'project.php';
require 'vendor/autoload.php';

$loader = new \Twig\Loader\FilesystemLoader(Project::webRootDirectory() . '/templates');

$cache = (Project::debuggingMode() == false) ? Project::webRootDirectory() ."/tmp/cache" : false;

$twig = new \Twig\Environment($loader, [
    'cache' => $cache,
]);

?>
