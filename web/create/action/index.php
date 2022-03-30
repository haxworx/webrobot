<?php

require_once '../../lib/project.php';
require_once 'lib/Database.php';
require 'lib/Twig.php';
require_once 'lib/Timer.php';
require_once 'lib/Config.php';
require_once 'lib/Session.php';

/*
  * Parse the form generated by /create.php.
  * Create a database entry for a job to be run.
  * Check for duplicates in the database table.
  * Ensure the input data is valid for our database table.
  * If any input is suspicious raise a 500 Internal Server Error.
  * Javascript client-side validation *should* also exist.
*/


$session = new Session;
if (!$session->authorized()) {
    http_response_code(401);
    exit(1);
}

$session->start();

if ((!isset($_POST['token'])) || ($_POST['token'] !== $session->getToken())) {
    http_response_code(405);
    return;
}

include 'form/create_edit.php';

if (!$validated) return;

try {
    $db = new DB();
} catch (Exception $e) {
    error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

try {
    $config = new Config();
    $max_robots = $config->options['max_crawlers'];

    $SQL = "SELECT COUNT(*) AS count FROM tbl_crawl_settings";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute();
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res['count'] >= $max_robots) {
        echo $template->render(['message' => 'Reached maximum robots allowed.']);
        return;
    }

    $SQL = "SELECT COUNT(*) AS count FROM tbl_crawl_settings WHERE address = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$address]);
    $res = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($res['count'] != 0) {
        echo $template->render(['message' => 'Address already exists.']);
        return;
    }
} catch (Exception $e) {
    error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

$db->pdo->beginTransaction();

try {
    $SQL = "
    INSERT INTO tbl_crawl_settings
    (user_id, scheme, address, domain, start_time, agent, weekly,
     daily, weekday, delay, ignore_query, import_sitemaps, retry_max)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$session->user_id, $scheme, $address, $domain, $start_time, $agent, $weekly,
	            $daily, $weekday, $delay, $ignore_query, $import_sitemaps, $retry_max
    ]);
    $bot_id = $db->pdo->lastInsertId();
    foreach ($_POST['content_types'] as $content_id) {
        $SQL = "INSERT INTO tbl_crawl_allowed_content (bot_id, content_id) VALUES (?, ?)";
	$stmt = $db->pdo->prepare($SQL);
	$stmt->execute([$bot_id, $content_id]);
    }
} catch (Exception $e) {
    $db->pdo->rollback();
    error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

$db->pdo->commit();

$config = new Config();
$docker_image = $config->options['docker_image'];

$args = [
    'bot_id'       => $bot_id,
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
$timer->create();

# Redirect to landing page. All is "ok"
header('Location: /');

?>
