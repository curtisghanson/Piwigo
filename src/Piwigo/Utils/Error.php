<?php
namespace Piwigo\Utils;

/**
 * PwgError object can be returned from any web service function implementation.
 */
class Error
{
    private $code;
    private $codeText;

    public function __construct($code, $codeText)
    {
        if ($code >= 400 and $code < 600)
        {
            set_status_header($code, $codeText);
        }

        $this->code     = $code;
        $this->codeText = $codeText;
    }

    public function code()
    {
        return $this->code;
    }
    
    public function message()
    {
        return $this->codeText;
    }
}
