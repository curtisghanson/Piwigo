<?php
namespace Piwigo\Http\Encoder;

/**
 *
 * Base class for web service response encoder.
 */
abstract class AbstractResponseEncoder
{
    /** encodes the web service response to the appropriate output format
     * @param response mixed the unencoded result of a service method call
     */
    abstract function encodeResponse($response);

    /** default "Content-Type" http header for this kind of response format
     */
    abstract function getContentType();

    /**
     * returns true if the parameter is a 'struct' (php array type whose keys are
     * NOT consecutive integers starting with 0)
     */
    static function is_struct(&$data)
    {
        if (is_array($data))
        {
            if (range(0, count($data) - 1) !== array_keys($data))
            {
                # string keys, unordered, non-incremental keys, .. - whatever, make object
                return true;
            }
        }
    
        return false;
    }

    /**
     * removes all XML formatting from $response (named array, named structs, etc)
     * usually called by every response encoder, except rest xml.
     */
    static function flattenResponse(&$value)
    {
        self::flatten($value);
    }

    private static function flatten(&$value)
    {
        if (is_object($value))
        {
            $class = @get_class($value);
      
            if (in_array($class, array('Piwigo\Utils\NamedArray', 'Piwigo\Utils\NamedStruct')))
            {
                $value = $value->_content;
            }
        }

        if (!is_array($value))
        {
            return;
        }

        if (self::is_struct($value))
        {
            if (isset($value[WS_XML_ATTRIBUTES]))
            {
                $value = array_merge($value, $value[WS_XML_ATTRIBUTES]);
                unset($value[WS_XML_ATTRIBUTES]);
            }
        }

        foreach ($value as $key=>&$v)
        {
            self::flatten($v);
        }
    }
}
