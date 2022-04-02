<?php

require_once 'project.php';
require_once 'Config.php';
require 'vendor/autoload.php';
use PhpMqtt\Client\Exceptions\MqttClientException;
use PhpMqtt\Client\MqttClient;

class Timer
{
    public $identifier;
    public $address;
    public $scheme;
    public $domain;
    public $agent;
    public $daily;
    public $weekday;
    public $time;
    public $docker_image;

    public function __construct($args)
    {
        $this->bot_id = $args['bot_id'];
        $this->scheme = $args['scheme'];
        $this->domain = $args['domain'];
        $this->address = $args['address'];
        $this->agent = $args['agent'];
        $this->daily = $args['daily'];
        $this->weekday = $args['weekday'];
        $this->time = $args['time'];
        $this->docker_image = $args['docker_image'];
        $this->identifier = $this->domain . '.' . $this->scheme;
    }

    private function dayString()
    {
        $day = mb_substr($this->weekday, 0, 3);
        $day = ucfirst($day);
        return $day;
    }

    public function update()
    {
        $this->remove($this->bot_id, $this->scheme, $this->domain);
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

        $executable = Project::rootDirectory() . '/robot_start.py';

        $data =
        "[Unit]\n".
        "Description=Web Robot deployment service ($this->address).\n" .
        "Wants=$this->identifier.timer\n" .
        "\n" .
        "[Service]\n" .
        "Type=oneshot\n" .
        "# We log to SQL.\n" .
        "StandardOutput=null\n" .
        "ExecStart=docker run --rm $this->docker_image $this->bot_id\n" .
        "\n";

        $path = "$dir/$this->identifier.service";
        $tmpname = tempnam("/tmp", "SPIDER");

        $f = fopen($tmpname, 'w');
        if ($f !== false) {
            fprintf($f, $data);
            fclose($f);
            chmod($tmpname, 0644);
            system("sudo -u spider cp $tmpname $path");
        }

        if ($this->daily) {
            $onCalendar = "OnCalendar=*-*-* $this->time\n";
        } else {
            $day = $this->dayString();
            $onCalendar = "OnCalendar=$day *-* $this->time\n";
        }

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
        $tmpname = tempnam("/tmp", "SPIDER");

        $f = fopen($tmpname, 'w');
        if ($f !== false) {
            fprintf($f, $data);
            fclose($f);
            chmod($tmpname, 0644);
            system("sudo -u spider cp $tmpname $path");
            system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user enable $this->identifier.timer");
            system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user start $this->identifier.timer");
        }
    }

    public static function remove($bot_id, $scheme, $domain)
    {
        $files = [ "$domain.$scheme.service", "$domain.$scheme.timer" ];
        $home = "/home/spider";

        system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user stop $domain.$scheme.service");
        system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user disable $domain.$scheme.service");
        system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user stop $domain.$scheme.timer");
        system("sudo -u spider XDG_RUNTIME_DIR=/run/user/2222 systemctl --user disable $domain.$scheme.timer");

        foreach ($files as $file) {
            $dir = $home . '/.config/systemd/user';
            $path = $dir . "/$file";
            system("sudo -u spider rm $path");
#            unlink($path);
        }

        $config = new Config();

        # Send terminate command to robot over MQTT.
        # If robot is running it will gracefully shutdown.
        try {
            $mqtt = new MqttClient($config->options['mqtt_host'], $config->options['mqtt_port'], 'robot_controller');
            $mqtt->connect();
            $mqtt->publish($config->options['mqtt_topic'], "TERMINATE: $bot_id", 0);
            $mqtt->disconnect();
        } catch (MqttClientException $e) {
            error_log(__FILE__ . ':'. __LINE__ . ':' . $e->getMessage());
        }
    }
}
