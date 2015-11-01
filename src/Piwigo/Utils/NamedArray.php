<?php
namespace Piwigo\Utils;

/**
 * Simple wrapper around an array (keys are consecutive integers starting at 0).
 * Provides naming clues for xml output (xml attributes vs. xml child elements?)
 * Usually returned by web service function implementation.
 */
class NamedArray
{
    private $content;
    private $itemName;
    private $xmlAttributes;

    /**
     * Constructs a named array
     * @param arr array (keys must be consecutive integers starting at 0)
     * @param itemName string xml element name for values of arr (e.g. image)
     * @param xmlAttributes array of sub-item attributes that will be encoded as
     *      xml attributes instead of xml child elements
     */
    public function __construct($arr, $itemName, $xmlAttributes = array())
    {
        $this->content       = $arr;
        $this->itemName      = $itemName;
        $this->xmlAttributes = array_flip($xmlAttributes);
    }
}
