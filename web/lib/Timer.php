<?php

require_once 'common.php';

# XXX: This needs more error checking.
# Create a SystemD timer unit/service.
# Create() and Remove().

class Timer
{
	public $identifier;
	public $address;
	public $agent;
	public $daily;
	public $weekday;
	public $time;

	public function __construct($args)
	{
		$this->identifier = $args['domain'];
		$this->address = $args['address'];
		$this->agent = $args['agent'];
		$this->daily = $args['daily'];
		$this->weekday = $args['weekday'];
		$this->time = $args['time'];
	}

	private function DayString()
	{
		$day = mb_substr($this->weekday, 0, 3);
		$day = ucfirst($day);
		return $day;
	}

	public function Create()
	{
		$home = getenv('HOME');
		$dir = $home . '/.config/systemd/user';
		if (!file_exists($dir)) {
			if (!mkdir($dir, 0755, true)) {
				error_log(__FILE__ . ':' . __LINE__ . ':' . "Unable to create directory: $dir\n";
			}
		}

		$executable = project_root_directory() . '/robot_start.py';

		$data =
		"[Unit]\n".
		"Description=Web Robot deployment service\n" .
		"Wants=$this->identifier.timer\n" .
		"\n" .
		"[Service]\n" .
		"Type=oneshot\n" .
		"ExecStart=python3 $executable\n" .
		"\n" .
		"[Install]\n" .
		"WantedBy=multi-user.target\n";

		$path = "$dir/$this->identifier.service";
		$f = fopen($path, 'w');
		if ($f !== false) {
			fprintf($f, $data);
			fclose($f);
		}
	
		if ($this->daily) {
			$onCalendar = "OnCalendar=*-*-* $this->time:00\n";
		} else {
			$day = $this->DayString();
			$onCalendar = "OnCalendar=$day *-* $this->time:00\n";
		}

		$data =
		"[Unit]\n" .
		"Description=Web Robot deployment timer.\n" .
		"Requires=$this->identifier.service\n" .
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

	public static function Remove($domain)
	{
        	$files = [ $domain . ".service", $domain . ".timer" ];
		$home = getenv('HOME');

		system("systemctl --user stop $domain.timer");
		system("systemctl --user disable $domain.timer");

		foreach ($files as $file) {
			$dir = $home . '/.config/systemd/user';
		        $path = $dir . "/$file";
			unlink($path);
		}
	}
};
