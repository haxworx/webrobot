<?php

const AUTHORIZED_SECRET = 'supersecret';

class Session
{
    public function __construct()
    {
    }

    public function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_set_cookie_params([
                'domain'   => $_SERVER['SERVER_NAME'],
                'secure'   => true,
                'lifetime' => 0,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict',
            ]);
            session_start();
            $this->IPAddress = $_SERVER['REMOTE_ADDR'];
            $this->UserAgent = $_SERVER['HTTP_USER_AGENT'];
            $this->modified = time();
        }
        if (!isset($this->token)) {
            $this->setToken();
        }
    }

    public function startExtend()
    {
        $this->extend();
    }

    public function authenticated($user_id)
    {
        # Change our authentication status.
        # Destroy our previous session and create new.
        $this->destroy();
        $this->start();
        $this->authorized = AUTHORIZED_SECRET;
        $this->user_id = $user_id;
        # Reset our CSRF token.
        $this->setToken();
    }

    public function IsAuthorized()
    {
        session_start();

        if (($this->IPAddress !== $_SERVER['REMOTE_ADDR']) || ($this->UserAgent !== $_SERVER['HTTP_USER_AGENT'])) {
            $this->destroy();
            return false;
        }

        if ((!isset($this->authorized)) || ($this->authorized !== AUTHORIZED_SECRET)) {
            $this->destroy();
            return false;
        }

        return true;
    }

    public function extend()
    {
        session_start();
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
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        setcookie(session_name(), FALSE, time() - 3600, '/', $_SERVER['SERVER_NAME'], true, true);
        session_unset();
        session_destroy();
        unset($_SESSION);
        return true;
    }
}

?>
