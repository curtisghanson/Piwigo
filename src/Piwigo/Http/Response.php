<?php
namespace Piwigo\Http;

use Piwigo\Http\Message;

class Response extends Message
{
    public function setCookie($key, $val, $expire = null)
    {
        if (null == $val or 0 === $expire)
        {
            unset($_COOKIE['pwg_' . $key]);
            
            return setcookie('pwg_' . $key, false, 0, $this->cookiePath);
        }

        $_COOKIE['pwg_' . $key] = $value;
        $expire = is_numeric($expire) ? $expire : strtotime('+10 years');
        
        return setcookie('pwg_' . $key, $val, $expire, $this->cookiePath);
    }
}
