<?php

require_once '../../lib/project.php';
require_once 'lib/Database.php';
require_once 'lib/Timer.php';
require_once 'lib/Session.php';

$session = new Session;
if (!$session->authorized()) {
    http_response_code(403);
    exit(1);
}

$session->start();

if ((!isset($_POST['token'])) || ($_POST['token'] !== $session->getToken())) {
    http_response_code(405);
    return;
}

if ((!isset($_POST['bot_id'])) || (empty($_POST['bot_id']))) {
    header("Location: /");
    return;
}

$bot_id = $_POST['bot_id'];

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
    $SQL = "DELETE FROM tbl_crawl_allowed_content WHERE bot_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$bot_id]);

    $SQL = "
    UPDATE tbl_crawl_settings SET
    start_time = ?, agent = ?, weekly = ?, daily = ?, weekday = ?,
    delay = ?, ignore_query = ?, import_sitemaps = ?, retry_max = ?
    WHERE bot_id = ?
    ";

    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([
        $start_time, $agent, $weekly, $daily, $weekday,
	$delay, $ignore_query, $import_sitemaps, $retry_max,
        $bot_id,
    ]);
    foreach ($_POST['content_types'] as $content_id) {
        $SQL = "INSERT INTO tbl_crawl_allowed_content (bot_id, content_id) VALUES (?, ?)";
        $stmt = $db->pdo->prepare($SQL);
        $stmt->execute([$bot_id, $content_id]);
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
    'bot_id'        => $bot_id,
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

header("Location: /edit/?bot_id=$bot_id");

?>
