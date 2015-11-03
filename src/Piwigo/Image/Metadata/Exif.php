<?php
namespace Piwigo\Image\Metadata;

class Exif
{
    /**
     * returns informations from EXIF metadata, mapping is done in this function.
     *
     * @param string $filename
     * @param array $map
     * @return array
     */
    public function getData($filename, $map)
    {
        global $conf;

        $result = array();

        if (!function_exists('read_exif_data'))
        {
            die('Exif extension not available, admin should disable exif use');
        }

        // Read EXIF data
        if ($exif = @read_exif_data($filename) or $exif2 = trigger_change('format_exif_data', $exif = null, $filename, $map))
        {
            if (!empty($exif2))
            {
                $exif = $exif2;
            }
            else
            {
                $exif = trigger_change('format_exif_data', $exif, $filename, $map);
            }

            // configured fields
            foreach ($map as $key => $field)
            {
                if (strpos($field, ';') === false)
                {
                    if (isset($exif[$field]))
                    {
                        $result[$key] = $exif[$field];
                    }
                }
                else
                {
                    $tokens = explode(';', $field);

                    if (isset($exif[$tokens[0]][$tokens[1]]))
                    {
                        $result[$key] = $exif[$tokens[0]][$tokens[1]];
                    }
                }
            }

            // GPS data
            $gps_exif = array_intersect_key($exif, array_flip(array('GPSLatitudeRef', 'GPSLatitude', 'GPSLongitudeRef', 'GPSLongitude')));

            if (count($gps_exif) == 4)
            {
                if (
                    is_array($gps_exif['GPSLatitude'])  and in_array($gps_exif['GPSLatitudeRef'], array('S', 'N')) and
                    is_array($gps_exif['GPSLongitude']) and in_array($gps_exif['GPSLongitudeRef'], array('W', 'E'))
                )
                {
                    $result['latitude']  = $this->parseGpsData($gps_exif['GPSLatitude'],  $gps_exif['GPSLatitudeRef']);
                    $result['longitude'] = $this->parseGpsData($gps_exif['GPSLongitude'], $gps_exif['GPSLongitudeRef']);
                }
            }
        }

        if (!$conf['allow_html_in_metadata'])
        {
            foreach ($result as $key => $value)
            {
                // in case the origin of the photo is unsecure (user upload), we remove
                // HTML tags to avoid XSS (malicious execution of javascript)
                $result[$key] = strip_tags($value);
            }
        }

        return $result;
    }

    /**
     * Converts EXIF GPS format to a float value.
     * @since 2.6
     *
     * @param string[] $raw eg:
     *    - 41/1
     *    - 54/1
     *    - 9843/500
     * @param string $ref 'S', 'N', 'E', 'W'. eg: 'N'
     * @return float eg: 41.905468
     */
    private function parseGpsData($raw, $ref)
    {
        foreach ($raw as &$i)
        {
            $i = explode('/', $i);
            $i = $i[1]==0 ? 0 : $i[0]/$i[1];
        }

        unset($i);

        $v = $raw[0] + $raw[1]/60 + $raw[2]/3600;

        $ref = strtoupper($ref);

        if ($ref == 'S' or $ref == 'W')
        {
            $v= -$v;
        }

        return $v;
    }
}
