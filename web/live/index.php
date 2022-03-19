<?php

require_once '../lib/common.php';
require_once 'lib/Database.php';
require_once 'lib/Twig.php';

$template = $twig->load('live.html.twig');

echo $template->render([]);

?>
