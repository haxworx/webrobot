<?php

namespace App\Tests\Utils;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use App\Entity\GlobalSettings;
use App\Entity\CrawlSettings;
use App\Utils\Timer;

class TimerTest extends TestCase
{
    public function testCreateDelete(): void
    {
        $globalSettings = new GlobalSettings;
        $globalSettings->setDockerImage('testing');
        $crawler = new CrawlSettings;
        $crawler->setBotId(999999);
        $crawler->setUserId(999999);
        $crawler->setDomain('localhost');
        $crawler->setAddress('https://localhost');
        $crawler->setScheme('https');
        $crawler->setStartTime(new \DateTime("NOW"));

        $timer = new Timer($globalSettings, $crawler);
        $files = $timer->getSystemdUnitFiles();

        $timer->create();

        // Timer systemd files created?
        foreach ($files as $file) {
            $path = $timer->getSaveDirectory() . '/' . $file;
            $this->assertTrue(file_exists($path));
        }
        // This test assume the developer has capability to sudo as spider user.
        $process = new Process(['sudo', '-E', '-u', 'spider', 'systemctl', '--user', 'list-timers', $timer->getIdentifier() . '.timer'], null, [
            'XDG_RUNTIME_DIR' => '/run/user/2222',
        ]);

        $found = false;
        $process->start();
        foreach ($process as $type => $data) {
            if (preg_match('/1 timer/', $data, $matches)) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found);

        $timer->remove();

        // Timer systemd files removed?
        foreach ($files as $file) {
            $path = $timer->getSaveDirectory() . '/' . $file;
            $this->assertTrue(!file_exists($path));
        }

        // This test assume the developer has capability to sudo as spider user.
        $process = new Process(['sudo', '-E', '-u', 'spider', 'systemctl', '--user', 'list-timers', $timer->getIdentifier() . '.timer'], null, [
            'XDG_RUNTIME_DIR' => '/run/user/2222',
        ]);

        $noTimer = false;
        $process->start();
        foreach ($process as $type => $data) {
            if (preg_match('/0 timer/', $data, $matches)) {
                $noTimer = true;
                break;
            }
        }
        $this->assertTrue($noTimer);
    }
}
