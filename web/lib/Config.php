<?php

require_once 'common.php';

class Config
{
	public $settings = [];
	public function __construct()
	{
		$path = project_config_path();
		$ini = parse_ini_file($path, true);
		if (!$ini) {
			throw new Exception("parse_ini_file");
		}
		$this->settings = $ini;
	}
}
