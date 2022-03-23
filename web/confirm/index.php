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

# Basic lock down of action against path.
$action_path = project::web_root_directory() . "/$action";
if ((!file_exists($action_path)) or (!is_dir($action_path))) {
    $template = $twig->load('errors.html.twig');
    echo $template->render(['message' => 'Invalid action']);
    return;

}

$template = $twig->load('confirm.html.twig');

echo $template->render(['action' => $action, 'params' => $params]);

?>
