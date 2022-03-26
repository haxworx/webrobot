<?php

class Session
{
    public function __construct()
    {
    }

    public function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'lifetime' => 3600,
                'domain'   => 'localhost',
                'secure'   => true,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
            $this->modified = time();
        }

        if (!isset($this->token)) {
            $this->setToken();
        }
    }

    public function startExtend()
    {
        $this->start();
        $this->extend();
    }

    public function extend()
    {
        setcookie(session_name(), session_id(), [
            'expires' => time() + 3600,
            'path' => '/',
            'secure' => true,
            'httponly' => true,
            'samesite' => 'Strict',
        ]);

        $this->modified = time();
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
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_destroy();
            unset($_SESSION);
            return true;
        }
        return false;
    }
}

?>
