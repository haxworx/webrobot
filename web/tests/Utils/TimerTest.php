<?php

namespace App\Tests\Utils;

use PHPUnit\Framework\TestCase;
use App\Entity\GlobalSettings;
use App\Entity\CrawlSettings;
use App\Utils\Timer;

class TimerTest extends TestCase
{
    public function testCreateDelete(): void
    {
        $globalSettings = new GlobalSettings;
        $globalSettings->setDockerImage('testing');
        $crawlSettings = new CrawlSettings;
        $crawlSettings->setBotId(999999);
        $crawlSettings->setUserId(999999);
        $crawlSettings->setDomain('localhost');
        $crawlSettings->setAddress('https://localhost');
        $crawlSettings->setScheme('https');
        $crawlSettings->setStartTime(new \DateTime("NOW"));

        $timer = new Timer($globalSettings, $crawlSettings);
        $files = $timer->getSystemdUnitFiles();

        $timer->create();

        foreach ($files as $file) {
            $path = $timer->getSaveDirectory() . '/' . $file;
            $this->assertTrue(file_exists($path));
        }

        $timer->remove();

        foreach ($files as $file) {
            $path = $timer->getSaveDirectory() . '/' . $file;
            $this->assertTrue(!file_exists($path));
        }
    }
}
