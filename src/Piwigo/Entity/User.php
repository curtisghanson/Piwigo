<?php

namespace Piwigo\Entity;

class User
{
    private $id;
    private $username;
    private $password;
    private $mailAddress;

    public function getId()
    {
        return $this->id;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    public function getPassword()
    {
        return $this->password;
    }

    public function setMailAddress($mailAddress)
    {
        $this->mailAddress = $mailAddress;

        return $this;
    }

    public function getMailAddress()
    {
        return $this->mailAddress;
    }
}