<?php

require_once '../lib/project.php';
require_once 'lib/Database.php';
require_once 'lib/Twig.php';
require_once 'lib/Session.php';

$session = new Session;
if (!$session->IsAuthorized()) {
    $session->destroy();
    header("Location: /login/");
    exit(0);
}
$session->startExtend();

if ((!isset($_GET['id'])) || (!preg_match('/^[0-9]+$/', $_GET['id']))) {
    http_response_code(500);
    return;
}

$record_id = intval($_GET['id']);

$data = null;
$type = null;

try {
    $db = new DB;
    $SQL = "SELECT content_type, data FROM tbl_crawl_data WHERE id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$record_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $data = $row['data'];
        $type = $row['content_type'];
    }

} catch (Exception $e) {
    error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

$data = strip_tags($data, '<h1><h2><h3><h4><p>');

header("Content-Type: text/plain");
echo $data;

?>
