<?php

namespace App\Utils;

use App\Entity\CrawlSettings;
use App\Entity\GlobalSettings;

class Timer
{
    private $identifier;
    private $botId;
    private $userId;
    private $address;
    private $scheme;
    private $domain;
    private $daily;
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

    public function update()
    {
        $this->remove();
        $this->create();
    }

    public static function getSaveDirectory(): string
    {
        return "/home/spider/.config/systemd/user";
    }

    public function create()
    {
        $dir = $this->getSaveDirectory();
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0755, true)) {
                error_log(__FILE__ . ':' . __LINE__ . ':' . "Unable to create directory: $dir\n");
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
        if ($f !== false) {
            fprintf($f, $data);
            fclose($f);
            chmod($tmpName, 0644);
            system("sudo -u spider cp $tmpName $path");
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
        if ($f !== false) {
            fprintf($f, $data);
            fclose($f);
            chmod($tmpName, 0644);
            system("sudo -u spider cp $tmpName $path");
            system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user enable $this->identifier.timer");
            system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user start $this->identifier.timer");
        }
    }

    public function remove()
    {
        $files = [ "$this->identifier.service", "$this->identifier.timer" ];

        foreach ($files as $file) {
            system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user stop $file");
            system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user disable $file");
            $dir = $this->getSaveDirectory();
            $path = $dir . "/$file";
            system("sudo -u spider rm $path");
        }
    }
}
