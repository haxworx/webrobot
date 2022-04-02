<?php

class Session
{
    public function __construct()
    {
        $this->setSecret('supersecret');
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
            session_regenerate_id();
            $this->setIpAddress($_SERVER['REMOTE_ADDR']);
            $this->setUserAgent($_SERVER['HTTP_USER_AGENT']);
            $this->setModified();
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
	$this->setAuthorizedSecret($this->getSecret());
        $this->setUserID($user_id);
        # Reset our CSRF token.
        $this->setToken();
    }

    public function IsAuthorized()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!$this->IsValid()) {
            return false;
        }

        if (!$this->UserIDValid()) {
            return false;
        }

        if ($this->getAuthorizedSecret() !== $this->getSecret()) {
            return false;
        }

        return true;
    }

    private function setAuthorizedSecret($secret)
    {
        $this->authorized_secret = $secret;
    }

    private function getAuthorizedSecret()
    {
        if (isset($this->authorized_secret)) {
            return $this->authorized_secret;
        }
	return false;
    }

    private function setSecret($secret)
    {
        $this->secret = $secret;
    }

    private function getSecret()
    {
        if (isset($this->secret)) {
            return $this->secret;
        }
	return false;
    }

    private function UserIDValid()
    {
        $user_id = $this->getUserID();
        if (($user_id === false) || (!is_int($user_id))) {
            return false;
        }
        return true;
    }

    public function IsValid()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (($this->getIpAddress() !== $_SERVER['REMOTE_ADDR']) || ($this->getUserAgent() !== $_SERVER['HTTP_USER_AGENT'])) {
            return false;
        }
        return true;
    }

    public function extend()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $this->setModified();
    }

    private function setUserID($user_id)
    {
        $this->user_id = $user_id;
    }

    public function getUserID()
    {
        if (isset($this->user_id)) {
            return $this->user_id;
        }
        return false;
    }

    public function setModified()
    {
        $this->modified = time();
    }

    public function getModified()
    {
        if (isset($this->modified)) {
            return $this->modified;
        }
        return false;
    }

    private function setIpAddress($address)
    {
        $this->ip_address = $address;
    }

    private function getIpAddress()
    {
         if (isset($this->ip_address)) {
             return $this->ip_address;
         }
         return false;
    }

    private function setUserAgent($user_agent)
    {
        $this->user_agent = $user_agent;
    }

    private function getUserAgent()
    {
        if (isset($this->user_agent)) {
            return $this->user_agent;
        }
        return false;
    }

    private function setToken()
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

    public function __toString()
    {
        $output = "";
        foreach ($_SESSION as $key => $value) {
            $output .= "$key => $value\n";
        }
        return $output;
    }

    public function destroy()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        setcookie(session_name(), "", [
            'domain'  => $_SERVER['SERVER_NAME'],
            'path'    => '/',
            'expires' => time() - 86400,
        ]);
        session_destroy();
        return true;
    }
}

?>
