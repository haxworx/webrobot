<?php

require_once '../lib/project.php';
require_once 'lib/Twig.php';
require_once 'lib/Database.php';

$content_types = [];

if ((!isset($_GET['botid'])) | ((empty($_GET['botid'])))) {
	echo "nooooo\n";
//    header("Location: /");
    return;
}

$botid = $_GET['botid'];

try {
    $db = new DB;
    $SQL = "SELECT botid, scheme, address, domain, agent,
	    delay, ignore_query, import_sitemaps, retry_max,
	    start_time, daily, weekly, weekday FROM tbl_crawl_settings
	    WHERE botid = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$botid]);
    $botrow = $stmt->fetch(PDO::FETCH_ASSOC);

    $contentids = [];
    $SQL = "SELECT contentid FROM tbl_crawl_allowed_content
	    WHERE botid = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$botrow['botid']]);
    while (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
        $contentids[] = intval($row['contentid']);
    }

    $content_types = [];
    $SQL = "SELECT contentid, content_type FROM tbl_content_types";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute();
    while (($row = $stmt->fetch(PDO::FETCH_ASSOC))) {
        $content_types[] = [
            'contentid' => $row['contentid'],
	    'content_type' => $row['content_type'],
	    'selected' => (in_array($row['contentid'], $contentids) ? true : false),
        ];
    }

} catch (Exception $e) {
    error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

$template = $twig->load('edit.html.twig');

echo $template->render(['bot' => $botrow, 'content_types' => $content_types ]);

?>
