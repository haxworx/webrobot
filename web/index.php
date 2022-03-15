<?php

require_once 'lib/Database.php';
require_once 'lib/Twig.php';

$robots = [];

try {
	$db = new DB;
	$SQL = "SELECT extid, domain, start_time, agent, weekday FROM tbl_crawl_launch";
	$stmt = $db->pdo->prepare($SQL);
	$stmt->execute();
	while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
		$robots[] = [ 'extid' => $row['extid'],
			      'domain' => $row['domain'],
			      'start_time' => $row['start_time'],
			      'agent' => $row['agent'],
			      'weekday' => $row['weekday']
		];
	}

} catch (Exception $e) {
	error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
	http_response_code(500);
	return;
}

$template = $twig->load('index.html.twig');

echo $template->render(['robots' => $robots ]);

?>
