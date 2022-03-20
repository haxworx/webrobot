<?php

require_once '../lib/project.php';
require_once 'lib/Twig.php';

$weekly = false;

if (isset($_GET['weekly'])) {
    $weekly = true;
}

$template = $twig->load('create.html.twig');

echo $template->render(['weekly' => $weekly ]);

?>
