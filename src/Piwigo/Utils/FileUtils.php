<?php
namespace Piwigo\Utils;

class FileUtils
{
    /**
     * returns the part of the string after the last "."
     *
     * @param string $filename
     * @return string
     */
    function getExtension($filename)
    {
      return substr(strrchr($filename, '.'), 1, strlen($filename));
    }
}
