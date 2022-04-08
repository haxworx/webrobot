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

if ((!isset($_GET['bot_id'])) || (!preg_match('/^[0-9]+$/', $_GET['bot_id']))) {
    header("Location: /");
    return;
}

$bot_id = intval($_GET['bot_id']);

try {
    $db = new DB;
    $SQL = "SELECT bot_id, scheme, address, domain, agent,
            delay, ignore_query, import_sitemaps, retry_max,
            start_time, daily, weekly, weekday FROM tbl_crawl_settings
            WHERE bot_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$bot_id]);
    $botrow = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($botrow === false) {
        error_log(__FILE__ . ':' . __LINE__ . ':' . 'No data');
        http_response_code(500);
        return;
    }

    $content_ids = [];
    $SQL = "SELECT content_id FROM tbl_crawl_allowed_content
            WHERE bot_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$botrow['bot_id']]);
    while (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
        $content_ids[] = intval($row['content_id']);
    }

    $content_types = [];
    $SQL = "SELECT content_id, content_type FROM tbl_content_types";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute();
    while (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
        $content_types[] = [
            'content_id' => $row['content_id'],
            'content_type' => $row['content_type'],
            'selected' => (in_array($row['content_id'], $content_ids) ? true : false),
        ];
    }

} catch (Exception $e) {
    error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

$template = $twig->load('edit.html.twig');

echo $template->render([
     'bot'           => $botrow,
     'content_types' => $content_types,
     'token'         => $session->getToken(),
]);

?>
