<?php

require_once 'project.php';

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
        $this->botid = $args['botid'];
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
        # Overwrite the unit timer and service files.
        $this->create();
    }

    public function create()
    {
        $home = getenv('HOME');
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
        "# We log to SQL and MQTT.\n" .
        "StandardOutput=null\n" .
        "StandardError=null\n" .
        "ExecStart=docker run $this->docker_image $this->botid\n" .
        "\n";

        $path = "$dir/$this->identifier.service";
        $f = fopen($path, 'w');
        if ($f !== false) {
            fprintf($f, $data);
            fclose($f);
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
        $f = fopen($path, 'w');
        if ($f !== false) {
            fprintf($f, $data);
            fclose($f);
            system("systemctl --user enable $this->identifier.timer");
            system("systemctl --user start $this->identifier.timer");
        }
    }

    public static function remove($scheme, $domain)
    {
        $files = [ "$domain.$scheme.service", "$domain.$scheme.timer" ];
        $home = getenv('HOME');

        system("systemctl --user stop $domain.$scheme.service");
        system("systemctl --user disable $domain.$scheme.service");
        system("systemctl --user stop $domain.$scheme.timer");
        system("systemctl --user disable $domain.$scheme.timer");

        foreach ($files as $file) {
            $dir = $home . '/.config/systemd/user';
            $path = $dir . "/$file";
            unlink($path);
        }
    }
};
