<?php

require_once 'project.php';
require_once 'aws/PasswordVault.php';
require_once 'lib/Database.php';

class Config
{
    public $options = [];
    public function __construct()
    {
        try {
            $db = new DB();
            $SQL = "SELECT id, time_stamp, in_use, max_crawlers, debug,
                    docker_image, mqtt_host, mqtt_port, mqtt_topic
                    FROM tbl_global_settings ORDER BY id DESC LIMIT 1";

            $stmt = $db->pdo->prepare($SQL);
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            error_log(__FILE__ . ':' . __LINE__ . ':' . $e->getMessage());
            http_response_code(500);
            return;
        }
        $this->options = $row;
    }
}
