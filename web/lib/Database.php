<?php

require_once 'project.php';
require_once 'Config.php';

class DB
{
    public $pdo;

    public function __construct($options = [])
    {
        $path = Project::configPath();
        $ini = parse_ini_file($path, true);
        if (!$ini) {
            throw new Exception("parse_ini_file");
        }

        if ($ini['aws']['password_vault'] == true) {
            try {
                $vault = new Vault($ini['aws']['profile'], $ini['aws']['region'], $ini['aws']['secret']);
                $db_host = $vault->contents['host'];
                $db_name = $vault->contents['dbname'];
                $db_user = $vault->contents['username'];
                $db_pass = $vault->contents['password'];
                $vault = null;
            } catch (Exception $e) {
                error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
            }
        } else {
            $db_host = $ini['database']['host'];
            $db_name = $ini['database']['name'];
            $db_user = $ini['database']['user'];
            $db_pass = $ini['database']['pass'];
        }

        $ini = null;

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
    }
}
?>
