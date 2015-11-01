<?php
namespace Piwigo\Http;

use Piwigo\Http\Message;

class Request extends Message
{
    public function getCookie($key, $default = null)
    {
        if (isset($_COOKIE['pwg_' . $key]))
        {
            return $_COOKIE['pwg_' . $key];
        }

        return $default;
    }
}
