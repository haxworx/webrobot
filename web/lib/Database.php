<?php

require_once 'common.php';
require_once 'Config.php';

class DB
{
	public $pdo;

	public function __construct($options = [])
	{
		$config = new Config();
		$db_host = $config->settings['database']['host'];
		$db_name = $config->settings['database']['name'];
		$db_user = $config->settings['database']['user'];
		$db_pass = $config->settings['database']['pass'];

		$default_options = [
			PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
			PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
			PDO::ATTR_EMULATE_PREPARES   => false,
		];
		$options = array_replace($default_options, $options);
		$dsn = "mysql:host=$db_host;dbname=$db_name;charset=utf8mb4";

		try {
			$this->pdo = new PDO($dsn, $db_user, $db_pass, $options);
		} catch (\PDOException $e) {
			throw new \PDOException($e->getMessage(), (int) $e->getCode());
		}
		$db_name = $db_pass = $db_user = $db_host = "";
		$config = null;
	}
}
?>
