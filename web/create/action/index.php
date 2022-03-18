<?php

require_once '../../lib/common.php';
require_once 'lib/Database.php';
require_once 'lib/Twig.php';
require_once 'lib/Timer.php';
require_once 'lib/Config.php';

/*
  * Parse the form generated by /create.php.
  * Create a database entry for a job to be run.
  * Check for duplicates in the database table.
  * Ensure the input data is valid for our database table.
  * If any input is suspicious raise a 500 Internal Server Error.
  * Javascript client-side validation *should* also exist.
*/


function valid_address($address)
{
	if (preg_match('/^(http|https):\/\/(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z0-9][a-z0-9-]{0,61}[a-z0-9]$/', strtolower($address))) {
		return true;
	}
	return false;
}

function valid_agent($agent)
{
	if (preg_match('/^[A-Za-z0-9\._\/]+\/\d+\.\d+$/', $agent)) {
		return true;
	}

	return false;
}

function valid_time($time)
{
	if (preg_match('/^\d{2}:\d{2}$/', $time)) {
		return true;
	}

	return false;
}

$allowed = [ 'address', 'agent', 'time', 'frequency'];
$frequency_allowed = [ 'daily', 'weekly' ];
$weekdays = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
$daily = 0;
$weekly = 0;

foreach ($allowed as $param) {
	if (!isset($_POST[$param]) || empty($_POST[$param])) {
		# Empty but valid field redirect to self.
		header("Location: /create");
		return;
	}

	if (!in_array($param, $allowed)) {
		http_response_code(500);
		return;
	}
}

$address     = $_POST['address'];
$agent       = $_POST['agent'];
$start_time  = $_POST['time'];
$frequency   = $_POST['frequency'];
$weekday     = null;

if (!in_array($frequency, $frequency_allowed)) {
	http_response_code(500);
	return;
}

try {
	$template = $twig->load('errors.html.twig');
} catch (Exception $e) {
	error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
	http_response_code(500);
	return;
}

$input_error = false;

if (!valid_time($start_time)) {
	$input_error = "Invalid time specified";
}

if (!valid_agent($agent)) {
	$input_error = "Invalid user-agent specified.";
}

if (!valid_address($address)) {
	$input_error = "Invalid address specified.";
}

if ($input_error !== false) {
	echo $template->render(['message' => $input_error, 'source_url' => '/create/']);
	return;
}

if ($frequency === "daily") {
	$daily = 1;
} elseif ($frequency === "weekly") {
	if (!isset($_POST['weekly']) || empty($_POST['weekly'])) {
		http_response_code(500);
		return;
	} else {
		if (!in_array($_POST['weekly'], $weekdays)) {
			http_response_code(500);
			return;
		}
		$weekly = 1;
		$weekday = $_POST['weekly'];
	}
}

$domain = parse_url($address, PHP_URL_HOST);
$scheme = parse_url($address, PHP_URL_SCHEME);

try {
	$config = new Config();
	$max_robots = $config->settings['main']['max_crawlers'];

	$db = new DB();

	$SQL = "SELECT COUNT(*) AS count FROM tbl_crawl_launch";
	$stmt = $db->pdo->prepare($SQL);
	$stmt->execute();
	$res = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($res['count'] >= $max_robots) {
		echo $template->render(['message' => 'Reached maximum robots allowed.']);
		return;
	}

	$SQL = "SELECT COUNT(*) AS count FROM tbl_crawl_launch WHERE address = ?";
	$stmt = $db->pdo->prepare($SQL);
	$stmt->execute([$address]);
	$res = $stmt->fetch(PDO::FETCH_ASSOC);
	if ($res['count'] != 0) {
		echo $template->render(['message' => 'Address already exists.']);
		return;
	}

	# MD5 sum of our data.
	$extid = md5($address . $domain . $start_time . $agent . $weekly . $daily . $weekday);
	$SQL = "
	INSERT IGNORE INTO tbl_crawl_launch
	(extid, scheme, address, domain, start_time, agent, weekly, daily, weekday)
	VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ";
	$stmt = $db->pdo->prepare($SQL);
	$stmt->execute([$extid, $scheme, $address, $domain, $start_time, $agent, $weekly, $daily, $weekday]);
} catch (Exception $e) {
	error_log(__FILE__ . ':' .  __LINE__ . ':' . $e->getMessage());
	http_response_code(500);
	return;
}

$config = new Config();
$docker_image = $config->settings['main']['docker_image'];

$args = [
	'domain'  => $domain,
	'address' => $address,
	'scheme'  => $scheme,
	'agent'   => $agent,
	'daily'   => $daily,
	'weekday' => $weekday,
	'time'    => $start_time,
	'docker_image' => $docker_image,
];

$timer = new Timer($args);
$timer->Create();

# Redirect to landing page. All is "ok"
header('Location: /');

?>