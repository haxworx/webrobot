<?php

require_once '../lib/common.php';
require_once 'lib/Twig.php';

$weekly = false;

if (isset($_GET['weekly'])) {
	$weekly = true;
}

$template = $twig->load('create.html.twig');

echo $template->render(['weekly' => $weekly ]);

?>
