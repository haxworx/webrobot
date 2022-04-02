<?php

require_once '../lib/project.php';
require_once 'lib/Twig.php';
require_once 'lib/Database.php';
require_once 'lib/Session.php';

$session = new Session;
if (!$session->IsAuthorized()) {
    $session->destroy();
    header("Location: /login/");
    exit(0);
}
$session->startExtend();

$content_types = [];

try {
    $db = new DB;
    $SQL = "SELECT content_id, content_type FROM tbl_content_types";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $content_types[] = [ 
	    'content_id' => $row['content_id'],
            'content_type' => $row['content_type'],
        ];
    }

} catch (Exception $e) {
    error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

$template = $twig->load('create.html.twig');

echo $template->render([
    'content_types' => $content_types,
    'token'         => $session->getToken(),
]);

?>
