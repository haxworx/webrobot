<?php

require_once '../lib/project.php';
require_once 'lib/Database.php';
require_once 'lib/Twig.php';

$params = [];

foreach ($_POST as $key => $value) {
    $params[] = ['key' => $key, 'value' => $value];
}

if (!isset($_POST['action']) || empty($_POST['action'])) {
    header("Location: /");
    return;
}

$action = $_POST['action'];
if (!preg_match('/^[a-zA-Z0-9]+$/', $action)) {
    header("Location: /");
    return;
}

$template = $twig->load('confirm.html.twig');

echo $template->render(['action' => $action, 'params' => $params]);

?>
