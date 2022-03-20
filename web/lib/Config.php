<?php

require_once 'project.php';
require_once 'aws/PasswordVault.php';

class Config
{
    public $settings = [];
    public function __construct()
    {
        $path = Project::config_path();
        $ini = parse_ini_file($path, true);
        if (!$ini) {
            throw new Exception("parse_ini_file");
        }

        $this->settings = $ini;
        if ($ini['aws']['password_vault'] == true) {
            try {
                $vault = new Vault($ini['aws']['profile'], $ini['aws']['region'], $ini['aws']['secret']);
                $this->settings['database']['db_host'] = $vault->contents['host'];
                $this->settings['database']['db_name'] = $vault->contents['dbname'];
                $this->settings['database']['db_user'] = $vault->contents['username'];
                $this->settings['database']['db_pass'] = $vault->contents['password'];
            } catch (Exception $e) {
                error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
            }
        } 
    }
}
