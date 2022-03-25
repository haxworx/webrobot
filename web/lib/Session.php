<?php

class Session
{
    const SESSION_STARTED = true;
    const SESSION_NOT_STARTED = false;

    private $session_state = self::SESSION_NOT_STARTED;

    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (!isset(self::$instance)) {
            self::$instance = new self;
        }

        self::$instance->startSession();

        return self::$instance;
    }


    public function startSession()
    {
        if ($this->session_state === self::SESSION_NOT_STARTED) {
            $this->session_state = session_start();
        }

        return $this->session_state;
    }

    public function setToken()
    {
	$this->token = bin2hex(random_bytes(32));
    }

    public function getToken()
    {
        if (isset($this->token)) {
            return $this->token;
        }
        return false;
    }

    public function __set($name, $value)
    {
        $_SESSION[$name] = $value;
    }

    public function __get($name)
    {
        if (isset($_SESSION[$name])) {
            return $_SESSION[$name];
        }
    }

    public function __isset($name)
    {
        return isset($_SESSION[$name]);
    }

    public function __unset($name)
    {
        unset($_SESSION[$name]); 
    }

    public function destroy()
    {
        if ($this->session_state == self::SESSION_STARTED) {
            $this->session_state = !session_destroy();
            unset($_SESSION);
            return !$this->session_state;
        }
        return false;
    }
}

?>
