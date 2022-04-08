<?php

require '../lib/project.php';
require_once 'lib/Database.php';
require_once 'lib/Timer.php';
require_once 'lib/Session.php';

# DELETE a job from the tbl_crawl_launch table.

$session = new Session;
if (!$session->IsAuthorized()) {
    $session->destroy();
    http_response_code(401);
    exit(0);
}

$session->startExtend();

if ((!isset($_POST['token'])) || ($_POST['token'] !== $session->getToken())) {
    http_response_code(405);
    return;
}

if (!isset($_POST['bot_id']) || (!preg_match('/^[0-9]+$/', $_POST['bot_id']))) {
    header("Location: / ");
    return;
}

$bot_id = intval($_POST['bot_id']);
$domain = false;
$scheme = false;

try {
    $db = new DB();

    $SQL = "SELECT domain, scheme FROM tbl_crawl_settings WHERE bot_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$bot_id]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($row !== false) {
        $domain = $row['domain'];
        $scheme = $row['scheme'];
    }

    $SQL = "DELETE FROM tbl_crawl_settings WHERE bot_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$bot_id]);

    $SQL = "DELETE FROM tbl_crawl_data WHERE bot_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$bot_id]);

    $SQL = "DELETE FROM tbl_crawl_errors WHERE bot_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$bot_id]);

    $SQL = "DELETE FROM tbl_crawl_allowed_content WHERE bot_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$bot_id]);
} catch (Exception $e) {
    error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
}

if (($domain !== false) && ($scheme !== false)) {
    Timer::remove($bot_id, $scheme, $domain);
} else {
    error_log(__FILE__ . ':' . __LINE__ . ':' . 'Failed to remove timer unit.');
}

header("Location: /");
?>
