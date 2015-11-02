<?php
namespace Piwigo\Security;

use Piwigo\Entity\User;

class Authorization
{
    const ACCESS_FREE          = 0;
    const ACCESS_GUEST         = 1;
    const ACCESS_CLASSIC       = 2;
    const ACCESS_ADMINISTRATOR = 3;
    const ACCESS_WEBMASTER     = 4;
    const ACCESS_CLOSED        = 5;

    private $user;
    private $guestUser;

    public function __construct(User $user, $conf)
    {
        $this->user      = $user;
        $this->guestUser = $conf['guest_access'];
    }

    public function isGranted($role, $status)
    {
        if (!$this->isGrantedByStatus($role, $status))
        {
            access_denied();
        }
    }

    public function isGrantedByStatus($role, $status = '')
    {
        return $this->getRoleStatus($status) >= $role;
    }

    public function getRoleStatus($status = '')
    {
        global $conf;

        switch ($this->getUserStatus($status))
        {
        case 'guest':
            return $conf['guest_access'] ? self::ACCESS_GUEST : self::ACCESS_FREE;
            break;
        case 'generic':
            return self::ACCESS_GUEST;
            break;
        case 'normal':
            return self::ACCESS_CLASSIC;
            break;
        case 'admin':
            return self::ACCESS_ADMINISTRATOR;
            break;
        case 'webmaster':
            return self::ACCESS_WEBMASTER;
            break;
        default:
            return self::ACCESS_FREE;
            break;
        }
    }

    public function getUserStatus($status = '')
    {
        if (empty($status))
        {
            return !empty($this->user->getStatus()) ? $this->user->getStatus() : '';
        }

        return $status;
    }
}
