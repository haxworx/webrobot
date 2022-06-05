<?php

namespace App\Service;

use App\Entity\GlobalSettings;
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\MqttClient;

class Mqtt extends MqttClient
{
    const COMMAND_TYPE = "command";
    const MESSAGE_TYPE = "message";
    const CLIENT_ID = "robot_controller";
    private $host = "";
    private $port = 1883;
    private $topic = "default";

    public function __construct(GlobalSettings $globalSettings)
    {
        $this->host = $globalSettings->getMqttHost();
        $this->port = $globalSettings->getMqttPort();
        $this->topic = $globalSettings->getMqttTopic();
        parent::__construct($this->host, $this->port, self::CLIENT_ID);
        try {
            $this->connect();
        } catch (MqttClientException $e) {
        }
    }

    private function payload(int $botId, string $type, string $command = "", string $message = "")
    {
        $payload = [
            'type'      => $type,
            'command'   => $command,
            'message'   => $message,
            'author'    => self::CLIENT_ID,
            'timestamp' => new \DateTime('NOW'),
            'bot_id'    => $botId,
        ];

        return json_encode($payload, JSON_PRETTY_PRINT);
    }

    public function sendMessage(int $botId, string $message)
    {
        parent::publish($this->topic, $this->payload($botId, self::MESSAGE_TYPE, message: $message));
    }

    public function sendCommand(int $botId, string $command)
    {
        parent::publish($this->topic, $this->payload($botId, self::COMMAND_TYPE, command: $command));
    }

    public function stopRobot(int $botId)
    {
        $this->sendCommand($botId, "terminate");
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
