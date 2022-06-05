<?php

namespace App\Tests\Service;

use App\Service\Mqtt;
use App\Entity\GlobalSettings;

use PHPUnit\Framework\TestCase;

class MqttTest extends TestCase
{
    public function testMqtt(): void
    {
        $globalSettings = new GlobalSettings;
        $globalSettings->setMqttHost('localhost');
        $globalSettings->setMqttPort(1883);
        $globalSettings->setMqttTopic('testing');

        $mqtt = new Mqtt($globalSettings);
        $mqtt->stopRobot(1);

        $this->assertTrue(true);
    }
}
