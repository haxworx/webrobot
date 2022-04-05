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

$wanted_domain = false;
$search_term = false;

if (isset($_GET['domain'])) {
    if (preg_match('/^[A-Za-z0-9\.]+$/', $_GET['domain'])) {
        $wanted_domain = $_GET['domain'];
    }
}

if (isset($_GET['search'])) {
    if (preg_match('/[A-Za-z0-9 ]+$/', $_GET['search'])) {
        $search_term = $_GET['search'];
    }
}

$robot_ids = [];
$results = [];

try {
    $db = new DB;
} catch (Exception $e) {
    error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

try {
    $SQL = "SELECT bot_id FROM tbl_crawl_settings WHERE user_id = ?";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute([$session->getUserID()]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $robot_ids[] = $row['bot_id'];
    }
} catch (Exception $e) {
    error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

$domains = [];

try {
    $SQL = "SELECT distinct(domain) FROM tbl_crawl_data WHERE bot_id IN (" .implode(",", $robot_ids) . ")";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $domains[] = $row['domain'];
    }
} catch (Exception $e) {
    error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

if (($wanted_domain !== false) && ($search_term !== false)) {
    try {
        $SQL = "SELECT id, url FROM tbl_crawl_data WHERE domain = ? AND data LIKE ?";
        $stmt = $db->pdo->prepare($SQL);
        $stmt->execute([$wanted_domain, '%'. $search_term . '%']);
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
    'domains'        => $domains,
    'search'         => $search_term,
    'wanted_domain'  => $wanted_domain,
    'results'        => $results,
    'results_count'  => count($results),
]);

?>