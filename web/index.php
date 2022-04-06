<?php

require_once 'lib/Database.php';
require_once 'lib/Twig.php';
require_once 'lib/Session.php';
require_once 'lib/Common.php';

$session = new Session;
if (!$session->IsAuthorized()) {
    $session->destroy();
    header("Location: /login/");
    exit(0);
}
$session->startExtend();

$robots = [];

try {
    $db = new DB;
    $SQL = "SELECT bot_id, scheme, address, domain, start_time, end_time, agent, weekday FROM tbl_crawl_settings";
    $stmt = $db->pdo->prepare($SQL);
    $stmt->execute();
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $robots[] = [
            'bot_id'      => $row['bot_id'],
            'scheme'      => $row['scheme'],
            'address'     => $row['address'],
            'domain'      => $row['domain'],
            'start_time'  => $row['start_time'],
            'end_time'    => Common::FuzzyDateTime($row['end_time']),
            'agent'       => $row['agent'],
            'weekday'     => $row['weekday']
        ];
    }

} catch (Exception $e) {
    error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
    http_response_code(500);
    return;
}

$template = $twig->load('index.html.twig');

echo $template->render([
    'robots' => $robots,
    'token'  => $session->getToken(),
]);

?>
