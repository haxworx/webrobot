<?php

require_once '../lib/project.php';
require_once 'lib/Database.php';
require_once 'lib/Twig.php';
require_once 'lib/Session.php';

$session = Session::getInstance();

$template = $twig->load('live.html.twig');

echo $template->render([]);

?>
