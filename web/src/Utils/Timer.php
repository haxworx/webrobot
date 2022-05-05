<?php

namespace App\Utils;

class Timer
{
    private $identifier;
    private $botId;
    private $userId;
    private $address;
    private $scheme;
    private $domain;
    private $agent;
    private $daily;
    private $time;
    private $docker_image;

    public function __construct($args)
    {
        $this->botId = $args['bot_id'];
        $this->userId = $args['user_id'];
        $this->scheme = $args['scheme'];
        $this->domain = $args['domain'];
        $this->address = $args['address'];
        $this->agent = $args['agent'];
        $this->time = $args['time'];
        $this->docker_image = $args['docker_image'];
        $this->identifier = $this->createIdentifier($this->botId, $this->userId, $this->scheme, $this->domain);
    }

    private static function createIdentifier($botId, $userId, $domain, $scheme)
    {
        return $botId . '.' . $userId . '.' . $scheme . '.' . $domain;
    }

    public function update()
    {
        $this->remove($this->botId, $this->userId, $this->scheme, $this->domain);
        $this->create();
    }

    public function create()
    {
        $home = "/home/spider";
        $dir = $home . '/.config/systemd/user';
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
        "ExecStart=docker run --rm $this->docker_image $this->botId\n" .
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

    public static function remove($botId, $userId, $scheme, $domain)
    {
        $identifier = self::createIdentifier($botId, $userId, $scheme, $domain);
        $files = [ "$identifier.service", "$identifier.timer" ];
        $home = "/home/spider";

        system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user stop $identifier.service");
        system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user disable $identifier.service");
        system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user stop $identifier.timer");
        system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user disable $identifier.timer");

        foreach ($files as $file) {
            $dir = $home . '/.config/systemd/user';
            $path = $dir . "/$file";
            system("sudo -u spider rm $path");
        }
    }
}
