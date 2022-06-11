<?php

namespace App\Entity;

class UserSettings
{
    private $oldPassword;
    private $plainPassword;
    private $confirmPassword;

    public function setOldPassword(string $value)
    {
        $this->oldPassword = $value;
    }

    public function getOldPassword()
    {
        return $this->oldPassword;
    }

    public function setPlainPassword(string $value)
    {
        $this->plainPassword = $value;
    }

    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setConfirmPassword(string $value)
    {
        $this->confirmPassword = $value;
    }

    public function getConfirmPassword()
    {
        return $this->confirmPassword;
    }
}
