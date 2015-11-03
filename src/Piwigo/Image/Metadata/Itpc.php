<?php
namespace Piwigo\Image\Metadata;

use Piwigo\Utils\StringUtils;

class Itpc
{
    /**
     * returns informations from IPTC metadata, mapping is done in this function.
     *
     * @param string $filename
     * @param array $map
     * @return array
     */
    function getData($filename, $map, $array_sep = ',')
    {
        global $conf;

        $result  = array();
        $imginfo = array();

        if (false == @getimagesize($filename, $imginfo))
        {
            return $result;
        }

        if (isset($imginfo['APP13']))
        {
            $iptc = iptcparse($imginfo['APP13']);

            if (is_array($iptc))
            {
                $rmap = array_flip($map);

                foreach (array_keys($rmap) as $iptc_key)
                {
                    if (isset($iptc[$iptc_key][0]))
                    {
                        if ($iptc_key == '2#025')
                        {
                            $value = implode($array_sep, array_map('Piwigo\Image\Metadata\Itpc::cleanValue', $iptc[$iptc_key]));
                        }
                        else
                        {
                            $value = self::cleanValue($iptc[$iptc_key][0]);
                        }

                        foreach (array_keys($map, $iptc_key) as $pwg_key)
                        {
                            $result[$pwg_key] = $value;

                            if (!$conf['allow_html_in_metadata'])
                            {
                                // in case the origin of the photo is unsecure (user upload), we
                                // remove HTML tags to avoid XSS (malicious execution of
                                // javascript)
                                $result[$pwg_key] = strip_tags($result[$pwg_key]);
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * return a cleaned IPTC value.
     *
     * @param string $value
     * @return string
     */
    public static function cleanValue($value)
    {
        // strip leading zeros (weird Kodak Scanner software)
        while (isset($value[0]) and $value[0] == chr(0))
        {
            $value = substr($value, 1);
        }

        // remove binary nulls
        $value = str_replace(chr(0x00), ' ', $value);

        if ( preg_match('/[\x80-\xff]/', $value) )
        {
            // apparently mac uses some MacRoman crap encoding. I don't know
            // how to detect it so a plugin should do the trick.
            $value = trigger_change('Piwigo\Image\Metadata\Itpc::cleanValue', $value);

            if (($qual = StringUtils::qualifyUtf8($value)) != 0)
            {
                // has non ascii chars
                if ($qual > 0)
                {
                    $input_encoding = 'utf-8';
                }
                else
                {
                    $input_encoding = 'iso-8859-1';

                    if (function_exists('iconv') or function_exists('mb_convert_encoding'))
                    {
                        // using windows-1252 because it supports additional characters
                        // such as "oe" in a single character (ligature). About the
                        // difference between Windows-1252 and ISO-8859-1: the characters
                        // 0x80-0x9F will not convert correctly. But these are control
                        // characters which are almost never used.
                        $input_encoding = 'windows-1252';
                    }
                }

                $value = convert_charset($value, $input_encoding, get_pwg_charset());
            }
        }

        return $value;
    }
}
