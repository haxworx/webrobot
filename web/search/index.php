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

$bot_id = false;
$search_term = false;

if (isset($_GET['bot_id'])) {
    if (preg_match('/^[0-9]+$/', $_GET['bot_id'])) {
        $bot_id = intval($_GET['bot_id']);
    }
}

if (isset($_GET['search'])) {
    if (preg_match('/[A-Za-z0-9 ]+$/', $_GET['search'])) {
        $search_term = $_GET['search'];
    }
}

$robots = [];
$results = [];

try {
    $db = new DB;
} catch (Exception $e) {
    error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

try {
    $SQL = "SELECT bot_id, address FROM tbl_crawl_settings WHERE user_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$session->getUserID()]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $robots[] = [
            'bot_id'  => $row['bot_id'],
            'address' => $row['address'],
        ];
    }
} catch (Exception $e) {
    error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

if (($bot_id !== false) && ($search_term !== false)) {
    try {
        $SQL = "SELECT id, url, scheme, domain FROM tbl_crawl_data WHERE bot_id = ? AND data LIKE ?";
        $stmt = $db->pdo->prepare($SQL);
        $stmt->execute([$bot_id, '%'. $search_term . '%']);
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $results[] = [ 'id' => $row['id'], 'url' => $row['url'] ];
        }
    } catch (Exception $e) {
        error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
        http_response_code(500);
        return;
    }

}

$template = $twig->load('search.html.twig');

echo $template->render([
    'token'          => $session->getToken(),
    'robots'         => $robots,
    'search'         => $search_term,
    'bot_id'         => $bot_id,
    'results'        => $results,
    'results_count'  => count($results),
]);

?>
