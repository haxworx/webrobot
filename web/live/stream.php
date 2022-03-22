<?php

require_once '../lib/project.php';
require_once 'lib/Config.php';
require 'vendor/autoload.php';
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\MqttClient;

# Create a Stream of our MQTT logging in real-time.

$config = new Config();

$server    = $config->options['mqtt_host'];
$port      = $config->options['mqtt_port'];
$topic     = $config->options['mqtt_topic'];
$client_id = "insert_something_session_like_here";

header("Content-Type: text/plain");
header('Cache-Control: no-cache');

for ($i = 0; $i < 1024; $i++) {
    echo ' ';
}

ob_flush();
flush();
try {
    $mqtt = new MqttClient($server, $port, $client_id);
    $mqtt->connect();
    $mqtt->subscribe($topic, function ($topic, $message) {
        $message = rtrim($message, $characters = " \n\r\t\v\x00");
        echo "$topic: $message\n";
        ob_flush();
    }, 0);
    $mqtt->registerLoopEventHandler(function (MqttClient $mqtt, float $elapsedTime) {
        # // XXX
    });

    $mqtt->loop(true);

    ob_end_flush();
    $mqtt->disconnect();
} catch (MqttClientException $e) {
   echo "Nothing to be seen here.\n";
   ob_flush();
   error_log(__FILE__ . '.' . __LINE__ . ':' . $e->getMessage());
}

?>
