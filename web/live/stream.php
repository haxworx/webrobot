<?php

require_once '../lib/common.php';
require 'vendor/autoload.php';
header("Content-Type: text/plain");
header('Cache-Control: no-cache');
$server = 'datacentre';
$port   = 1883;
$clientId = 'insert_here';

for ($i = 0; $i < 1024; $i++) {
  echo ' ';
}
ob_flush();
flush();
$mqtt = new \PhpMqtt\Client\MqttClient($server, $port, $clientId);
$mqtt->connect();
$mqtt->subscribe('testing', function ($topic, $message) {
   echo "$topic: $message\n";
   ob_flush();
}, 0);

$mqtt->loop(true);
ob_end_flush();
$mqtt->disconnect();


?>
