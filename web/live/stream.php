<?php

require_once '../lib/common.php';
require_once 'lib/Config.php';
require 'vendor/autoload.php';

# Create a Stream of our MQTT logging in real-time.

$config = new Config();

$server    = $config->settings['mqtt']['host'];
$port      = intval($config->settings['mqtt']['port']);
$topic     = $config->settings['mqtt']['topic'];
$client_id = "insert_something_session_like_here";

header("Content-Type: text/plain");
header('Cache-Control: no-cache');

for ($i = 0; $i < 1024; $i++) {
	echo ' ';
}

ob_flush();
flush();

$mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $client_id);
$mqtt->connect();
$mqtt->subscribe($topic, function ($topic, $message) {
	$message = rtrim($message, $characters = " \n\r\t\v\x00");
	echo "$topic: $message\n";
	ob_flush();
}, 0);

$mqtt->loop(true);

ob_end_flush();
$mqtt->disconnect();

?>
