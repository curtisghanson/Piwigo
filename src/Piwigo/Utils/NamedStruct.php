<?php
namespace Piwigo\Utils;

/**
 * Simple wrapper around a "struct" (php array whose keys are not consecutive
 * integers starting at 0). Provides naming clues for xml output (what is xml
 * attributes and what is element)
 */
class NamedStruct
{
    private $content;
    private $xmlAttributes;

    /**
     * Constructs a named struct (usually returned by web service function
     * implementation)
     * @param name string - containing xml element name
     * @param content array - the actual content (php array)
     * @param xmlAttributes array - name of the keys in $content that will be
     *    encoded as xml attributes (if null - automatically prefer xml attributes
     *    whenever possible)
     */
    public function __construct($content, $xmlAttributes = null, $xmlElements = null )
    {
        $this->content = $content;
    
        if (isset($xmlAttributes))
        {
            $this->xmlAttributes = array_flip($xmlAttributes);
        }
        else
        {
            $this->xmlAttributes = array();
      
            foreach ($this->content as $key => $value)
            {
                if (!empty($key) and (is_scalar($value) or is_null($value)))
                {
                    if (empty($xmlElements) or !in_array($key, $xmlElements))
                    {
                        $this->xmlAttributes[$key] = 1;
                    }
                }
            }
        }
    }
}
