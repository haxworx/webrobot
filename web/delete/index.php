<?php

require '../lib/common.php';
require_once 'lib/Database.php';
require_once 'lib/Timer.php';

# DELETE a job from the tbl_crawl_launch table.

if (!isset($_GET['extid']) || empty($_GET['extid'])) {
	header("Location: / ");
	return;
}

if (!isset($_GET['domain']) || empty($_GET['domain'])) {
	header("Location: /");
	return;
}
$extid = $_GET['extid'];
if (!preg_match('/^[A-Za-z0-9]{32}$/', $extid)) {
	header("Location: / ");
	return;
}

# XXX Check this.
$domain = $_GET['domain'];

try {
	$db = new DB();
	$SQL = "DELETE FROM tbl_crawl_launch WHERE extid = ?";
	$stmt = $db->pdo->prepare($SQL);
	$stmt->execute([$extid]);
} catch (Exception $e) {
	error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
}

Timer::Remove($domain);

header("Location: /");
?>
