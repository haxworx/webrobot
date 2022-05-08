<?php

namespace App\Utils;

use App\Entity\CrawlSettings;
use App\Entity\GlobalSettings;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class Timer
{
    private $identifier;
    private $botId;
    private $userId;
    private $address;
    private $scheme;
    private $domain;
    private $time;
    private $dockerImage;

    public function __construct(GlobalSettings $globalSettings, CrawlSettings $crawlSettings)
    {
        $this->botId       = $crawlSettings->getBotId();
        $this->userId      = $crawlSettings->getUserId();
        $this->domain      = $crawlSettings->getDomain();
        $this->address     = $crawlSettings->getAddress();
        $this->scheme      = $crawlSettings->getScheme();
        $this->time        = $crawlSettings->getStartTime()->format('H:i:s');
        $this->dockerImage = $globalSettings->getDockerImage();
        $this->identifier  = $this->createIdentifier($this->botId, $this->userId, $this->scheme, $this->domain);
    }

    private function createIdentifier($botId, $userId, $domain, $scheme): string
    {
        return $botId . '.' . $userId . '.' . $scheme . '.' . $domain;
    }

    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    public function update(): bool
    {
        $this->remove();
        return $this->create();
    }

    public static function getSaveDirectory(): string
    {
        return "/home/spider/.config/systemd/user";
    }

    public function getSystemdUnitFiles(): array
    {
        return [ "$this->identifier.service", "$this->identifier.timer" ];
    }

    public function create(): bool
    {
        $dir = $this->getSaveDirectory();
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0755, true)) {

                return false;
            }
        }

        $data =
        "[Unit]\n".
        "Description=Web Robot deployment service ($this->address).\n" .
        "Wants=$this->identifier.timer\n" .
        "\n" .
        "[Service]\n" .
        "Type=oneshot\n" .
        "# We log to SQL.\n" .
        "StandardOutput=null\n" .
        "ExecStart=docker run --rm $this->dockerImage $this->botId\n" .
        "\n";

        $path = "$dir/$this->identifier.service";
        $tmpName = tempnam("/tmp", "SPIDER");

        $f = fopen($tmpName, 'w');
        if ($f === false) {

            return false;
        } else {
            fprintf($f, $data);
            fclose($f);
            chmod($tmpName, 0644);
            $process = new Process(['sudo', '-u', 'spider', 'cp', $tmpName, $path]);
            try {
                $process->mustRun();
            } catch (ProcessFailedException $exception) {

                return false;
            }
        }

        $onCalendar = "OnCalendar=*-*-* $this->time\n";

        $data =
        "[Unit]\n" .
        "Description=Web Robot deployment timer ($this->address).\n" .
        "\n" .
        "[Timer]\n".
        "Unit=$this->identifier.service\n" .
        $onCalendar .
        "[Install]\n" .
        "WantedBy=timers.target\n";

        $path = "$dir/$this->identifier.timer";
        $tmpName = tempnam("/tmp", "SPIDER");

        $f = fopen($tmpName, 'w');
        if ($f === false) {

            return false;
        } else {
            fprintf($f, $data);
            fclose($f);
            chmod($tmpName, 0644);

            $process = new Process(['sudo', '-E', '-u', 'spider', 'cp', $tmpName, $path]);
            try {
                $process->mustRun();
            } catch (ProcessFailedException $exception) {

                return false;
            }

            $process = new Process(['sudo', '-E', '-u', 'spider', 'systemctl', '--user', 'enable', "$this->identifier.timer"], null, [
                'XDG_RUNTIME_DIR' => '/run/user/2222',
            ]);
            try {
                $process->mustRun();
            } catch (ProcessFailedException $exception) {

                return false;
            }

            $process = new Process(['sudo', '-E', '-u', 'spider', 'systemctl', '--user', 'start', "$this->identifier.timer"], null, [
                'XDG_RUNTIME_DIR' => '/run/user/2222',
            ]);
            try {
                $process->mustRun();
            } catch (ProcessFailedException $exception) {

                return false;
            }
        }

        return true;
    }

    public function remove()
    {
        $files = $this->getSystemdUnitFiles();

        foreach ($files as $file) {
            $process = new Process(['sudo', '-E', '-u', 'spider', 'systemctl', '--user', 'stop', $file], null, [
                'XDG_RUNTIME_DIR' => '/run/user/2222',
            ]);
            try {
                $process->mustRun();
            } catch (ProcessFailedException $exception) {

            }

            $process = new Process(['sudo', '-E', '-u', 'spider', 'systemctl', '--user', 'disable', $file], null, [
                'XDG_RUNTIME_DIR' => '/run/user/2222',
            ]);
            try {
                $process->mustRun();
            } catch (ProcessFailedException $exception) {

            }

            $dir = $this->getSaveDirectory();
            $path = $dir . "/$file";

            $process = new Process(['sudo', '-u', 'spider', 'rm', $path]);
            try {
                $process->mustRun();
            } catch (ProcessFailedException $exception) {

            }
        }
    }
}
