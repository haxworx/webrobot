<?php

require_once '../../lib/project.php';
require_once 'lib/Database.php';
require_once 'lib/Timer.php';
require_once 'lib/Session.php';

$session = new Session;
$session->start();

if ((!isset($_POST['token'])) || ($_POST['token'] !== $session->getToken())) {
    http_response_code(405);
    return;
}

if ((!isset($_POST['botid'])) || (empty($_POST['botid']))) {
    header("Location: /");
    return;
}

$botid = $_POST['botid'];

try {
    $db = new DB();
} catch (Exception $e) {
    error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

include 'form/create_edit.php';
if (!$validated) return;

$db->pdo->beginTransaction();

try {
    $SQL = "DELETE FROM tbl_crawl_allowed_content WHERE botid = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$botid]);

    $SQL = "
    UPDATE tbl_crawl_settings SET
    start_time = ?, agent = ?, weekly = ?, daily = ?, weekday = ?,
    delay = ?, ignore_query = ?, import_sitemaps = ?, retry_max = ?
    WHERE botid = ?
    ";

    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([
        $start_time, $agent, $weekly, $daily, $weekday,
	$delay, $ignore_query, $import_sitemaps, $retry_max,
        $botid,
    ]);
    foreach ($_POST['content_types'] as $contentid) {
        $SQL = "INSERT INTO tbl_crawl_allowed_content (botid, contentid) VALUES (?, ?)";
        $stmt = $db->pdo->prepare($SQL);
        $stmt->execute([$botid, $contentid]);
    }


} catch (Exception $e) {
    $db->pdo->rollback();
    error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

$db->pdo->commit();

$config = new Config();
$docker_image = $config->options['docker_image'];

$args = [
    'botid'        => $botid,
    'domain'       => $domain,
    'address'      => $address,
    'scheme'       => $scheme,
    'agent'        => $agent,
    'daily'        => $daily,
    'weekday'      => $weekday,
    'time'         => $start_time,
    'docker_image' => $docker_image,
];

$timer = new Timer($args);
$timer->update();

header("Location: /edit/?botid=$botid");

?>
