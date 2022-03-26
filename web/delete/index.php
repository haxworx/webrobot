<?php

require '../lib/project.php';
require_once 'lib/Database.php';
require_once 'lib/Timer.php';
require_once 'lib/Session.php';

# DELETE a job from the tbl_crawl_launch table.

$session = new Session;
$session->startExtend();

if ((!isset($_POST['token'])) || ($_POST['token'] !== $session->getToken())) {
    http_response_code(405);
    return;
}

if (!isset($_POST['botid']) || empty($_POST['botid'])) {
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

$botid  = $_POST['botid'];
$domain = $_POST['domain'];
$scheme = $_POST['scheme'];

try {
    $db = new DB();
    $SQL = "DELETE FROM tbl_crawl_settings WHERE botid = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$botid]);

    $SQL = "DELETE FROM tbl_crawl_allowed_content WHERE botid = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$botid]);
} catch (Exception $e) {
    error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
}

Timer::remove($scheme, $domain);

header("Location: /");
?>
