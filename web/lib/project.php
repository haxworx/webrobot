<?php

$path = get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/..');
set_include_path($path);

require_once 'Config.php';

class Project
{
    public static function root_directory()
    {
        static $path = NULL;

        if ($path === null) {
            $path = realpath(__DIR__ . '/../..');
        }
        return $path;
    }

    public static function web_root_directory()
    {
        static $path = null;

        if ($path === null) {
               $path = realpath(dirname(__FILE__) . '/..');
        }
        return $path;
    }

    public static function config_path()
    {
        static $path = null;

        # Base directory outside of document root.
        if ($path === null) {
            $path = realpath(__DIR__ . '/../../config.ini');
        }
        return $path;
    }

    public static function debugging_mode()
    {
        $config = new Config();

        return (bool) $config->settings['main']['debug'];
    }
}

?>
