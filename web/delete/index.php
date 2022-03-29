<?php

require '../lib/project.php';
require_once 'lib/Database.php';
require_once 'lib/Timer.php';
require_once 'lib/Session.php';

# DELETE a job from the tbl_crawl_launch table.

$session = new Session;
if (!$session->authorized()) {
    http_response_code(403);
    exit(0);
}

$session->startExtend();

if ((!isset($_POST['token'])) || ($_POST['token'] !== $session->getToken())) {
    http_response_code(405);
    return;
}

if (!isset($_POST['bot_id']) || empty($_POST['bot_id'])) {
    header("Location: / ");
    return;
}

if (!isset($_POST['scheme']) || empty($_POST['scheme'])) {
    header("Location: /");
    return;
}

if (!isset($_POST['domain']) || empty($_POST['domain'])) {
    header("Location: /");
    return;
}

$bot_id  = $_POST['bot_id'];
$domain = $_POST['domain'];
$scheme = $_POST['scheme'];

try {
    $db = new DB();
    $SQL = "DELETE FROM tbl_crawl_settings WHERE bot_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$bot_id]);

    $SQL = "DELETE FROM tbl_crawl_allowed_content WHERE bot_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$bot_id]);
} catch (Exception $e) {
    error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
}

Timer::remove($bot_id, $scheme, $domain);

header("Location: /");
?>
