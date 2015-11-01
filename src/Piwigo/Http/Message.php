<?php
namespace Piwigo\Http;

class Message
{
    private $cookiePath;

    public function __construct()
    {
        $this->buildCookiePath();
    }

    private function buildCookiePath()
    {
        if (isset($_SERVER['REDIRECT_SCRIPT_NAME']) and !empty($_SERVER['REDIRECT_SCRIPT_NAME']))
        {
            $scr = $_SERVER['REDIRECT_SCRIPT_NAME'];
        }
        else if (isset($_SERVER['REDIRECT_URL']))
        {
            // mod_rewrite is activated for upper level directories. we must set the
            // cookie to the path shown in the browser otherwise it will be discarded.
            if (
                        isset($_SERVER['PATH_INFO'])
                    and !empty($_SERVER['PATH_INFO'])
                    and ($_SERVER['REDIRECT_URL'] !== $_SERVER['PATH_INFO'])
                    and (substr($_SERVER['REDIRECT_URL'],-strlen($_SERVER['PATH_INFO'])) == $_SERVER['PATH_INFO'])
            )
            {
                $scr = substr($_SERVER['REDIRECT_URL'], 0,
                    strlen($_SERVER['REDIRECT_URL'])-strlen($_SERVER['PATH_INFO']));
            }
            else
            {
                $scr = $_SERVER['REDIRECT_URL'];
            }
        }
        else
        {
            $scr = $_SERVER['SCRIPT_NAME'];
        }

        $scr = substr($scr, 0, strrpos($scr, '/'));

        // add a trailing '/' if needed
        if ((strlen($scr) == 0) or ($scr{strlen($scr)-1} !== '/'))
        {
            $scr .= '/';
        }

        if (substr(PHPWG_ROOT_PATH, 0, 3) == '../')
        {
            // this is maybe a plugin inside pwg directory
            // TODO - what if it is an external script outside PWG ?
            $scr = $scr . PHPWG_ROOT_PATH;
        
            while (1)
            {
                $new = preg_replace('#[^/]+/\.\.(/|$)#', '', $scr);
          
                if ($new == $scr)
                {
                    break;
                }

                $scr = $new;
            }
        }

        $this->cookiePath = $scr;
    }

    public function getCookiePath()
    {
        return $this->cookiePath;
    }
}
