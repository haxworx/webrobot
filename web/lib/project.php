<?php

set_include_path(get_include_path() . PATH_SEPARATOR . realpath(__DIR__ . '/..'));

require_once 'Config.php';

class Project
{
    public static function rootDirectory()
    {
        static $path = NULL;

        if ($path === null) {
            $path = realpath(__DIR__ . '/../..');
        }
        return $path;
    }

    public static function webRootDirectory()
    {
        static $path = null;

        if ($path === null) {
               $path = realpath(__DIR__ . '/..');
        }
        return $path;
    }

    public static function configPath()
    {
        static $path = null;

        if ($path === null) {
            $path = realpath(__DIR__ . '/../../config.ini');
        }
        return $path;
    }

    public static function debuggingMode()
    {
        $config = new Config();
        return (bool) $config->options['debug'];
    }
}

?>
