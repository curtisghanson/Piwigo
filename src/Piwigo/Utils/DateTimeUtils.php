<?php
namespace Piwigo\Utils;

class DateTimeUtils
{
    /**
     * returns the current microsecond since Unix epoch
     *
     * @return int
     */
    public static function getMicroSeconds()
    {
        $t1 = explode(' ', microtime());
        $t2 = explode('.', $t1[0]);
        $t2 = $t1[1] . substr($t2[1], 0, 6);
  
        return $t2;
    }

    /**
     * returns a float value coresponding to the number of seconds since
     * the unix epoch (1st January 1970) and the microseconds are precised
     * e.g. 1052343429.89276600
     *
     * @return float
     */
    public static function getMoment()
    {
        return microtime(true);
    }

    /**
     * returns the number of seconds (with 3 decimals precision)
     * between the start time and the end time given
     *
     * @param float $start
     * @param float $end
     * @return string "$TIME s"
     */
    public static function getElapsedTime($start, $end)
    {
        return number_format($end - $start, 3, '.', ' ') . ' s';
    }

    /**
     * Computes the difference between two dates.
     * returns a DateInterval object or a stdClass with the same attributes
     * http://stephenharris.info/date-intervals-in-php-5-2
     *
     * @param DateTime $date1
     * @param DateTime $date2
     * @return DateInterval|stdClass
     */
    public static function dateDiff($date1, $date2)
    {
        if (version_compare(PHP_VERSION, '5.3.0') >= 0)
        {
            return $date1->diff($date2);
        }

        $diff = new stdClass();

        // Make sure $date1 is ealier
        $diff->invert = $date2 < $date1;
    
        if ($diff->invert)
        {
            list($date1, $date2) = array($date2, $date1);
        }

        // Calculate R values
        $R = ($date1 <= $date2 ? '+' : '-');
        $r = ($date1 <= $date2 ? '' : '-');

        // Calculate total days
        $diff->days = round(abs($date1->format('U') - $date2->format('U')) / 86400);

        // A leap year work around - consistent with DateInterval
        $leap_year = $date1->format('m-d') == '02-29';
        
        if ($leap_year)
        {
            $date1->modify('-1 day');
        }

        // Years, months, days, hours
        $periods = array(
            'years'  => -1,
            'months' => -1,
            'days'   => -1,
            'hours'  => -1
        );

        foreach ($periods as $period => &$i)
        {
            if ($period == 'days' && $leap_year)
            {
                $date1->modify('+1 day');
            }

            while ($date1 <= $date2)
            {
                $date1->modify('+1 ' . $period);
                $i++;
            }

            // Reset date and record increments
            $date1->modify('-1 ' . $period);
        }

        list($diff->y, $diff->m, $diff->d, $diff->h) = array_values($periods);

        // Minutes, seconds
        $diff->s = round(abs($date1->format('U') - $date2->format('U')));
        $diff->i = floor($diff->s / 60);
        $diff->s = $diff->s - $diff->i * 60;

        return $diff;
    }

    /**
     * converts a string into a DateTime object
     *
     * @param int|string timestamp or datetime string
     * @param string $format input format respecting date() syntax
     * @return DateTime|false
     */
    public static function str2DateTime($original, $format = null)
    {
        if (empty($original))
        {
            return false;
        }

        if ($original instanceof DateTime)
        {
            return $original;
        }

        // from known date format
        if (!empty($format) && version_compare(PHP_VERSION, '5.3.0') >= 0)
        {
            // ! char to reset fields to UNIX epoch
            return DateTime::createFromFormat('!'.$format, $original);
        }
        else
        {
            $t = trim($original, '0123456789');
        
            // from timestamp
            if (empty($t))
            {
                return new DateTime('@'.$original);
            }
            // from unknown date format (assuming something like Y-m-d H:i:s)
            else
            {
                $ymdhms = array();
                $tok    = strtok($original, '- :/');
            
                while ($tok !== false)
                {
                    $ymdhms[] = $tok;
                    $tok      = strtok('- :/');
                }

                if (count($ymdhms)<3)
                {
                    return false;
                }

                if (!isset($ymdhms[3]))
                {
                    $ymdhms[3] = 0;
                }
                
                if (!isset($ymdhms[4]))
                {
                    $ymdhms[4] = 0;
                }
                
                if (!isset($ymdhms[5]))
                {
                    $ymdhms[5] = 0;
                }

                $date = new DateTime();
                $date->setDate($ymdhms[0], $ymdhms[1], $ymdhms[2]);
                $date->setTime($ymdhms[3], $ymdhms[4], $ymdhms[5]);
          
                return $date;
            }
        }
    }

    /**
     * returns a formatted and localized date for display
     *
     * @param int|string timestamp or datetime string
     * @param array $show list of components displayed, default is ['day_name', 'day', 'month', 'year']
     *    THIS PARAMETER IS PLANNED TO CHANGE
     * @param string $format input format respecting date() syntax
     * @return string
     */
    function formatDate($original, $show = null, $format = null)
    {
        global $lang;

        $date = self::str2DateTime($original, $format);

        if (!$date)
        {
            return l10n('N/A');
        }

        if ($show === null || $show === true)
        {
            $show = array('day_name', 'day', 'month', 'year');
        }

        // TODO use IntlDateFormatter for proper i18n

        $print = '';
      
        if (in_array('day_name', $show))
        {
            $print .= $lang['day'][$date->format('w')] . ' ';
        }

        if (in_array('day', $show))
        {
            $print .= $date->format('j') . ' ';
        }

        if (in_array('month', $show))
        {
            $print .= $lang['month'][$date->format('n')] . ' ';
        }

        if (in_array('year', $show))
        {
            $print .= $date->format('Y') . ' ';
        }

        if (in_array('time', $show))
        {
            $temp = $date->format('H:i');
        
            if ($temp != '00:00')
            {
                $print .= $temp . ' ';
            }
        }

        return trim($print);
    }

    /**
     * Format a "From ... to ..." string from two dates
     * @param string $from
     * @param string $to
     * @param boolean $full
     * @return string
     */
    public static function formatFromTo($from, $to, $full = false)
    {
        $from = self::str2DateTime($from);
        $to   = self::str2DateTime($to);

        if ($from->format('Y-m-d') == $to->format('Y-m-d'))
        {
            return self::formatDate($from);
        }
        else
        {
            if ($full || $from->format('Y') != $to->format('Y'))
            {
                $from_str = self::formatDate($from);
            }
            else if ($from->format('m') != $to->format('m'))
            {
                $from_str = self::formatDate($from, array('day_name', 'day', 'month'));
            }
            else
            {
                $from_str = self::formatDate($from, array('day_name', 'day'));
            }
        
            $to_str = self::formatDate($to);

            return l10n('from %s to %s', $from_str, $to_str);
        }
    }

    /**
     * Works out the time since the given date
     *
     * @param int|string timestamp or datetime string
     * @param string $stop year,month,week,day,hour,minute,second
     * @param string $format input format respecting date() syntax
     * @param bool $with_text append "ago" or "in the future"
     * @param bool $with_weeks
     * @return string
     */
    function timeSince($original, $stop='minute', $format=null, $with_text=true, $with_week=true)
    {
        $date = self::str2DateTime($original, $format);

        if (!$date)
        {
            return l10n('N/A');
        }

        $now  = new DateTime();
        $diff = self::dateDiff($now, $date);

        $chunks = array(
            'year'   => $diff->y,
            'month'  => $diff->m,
            'week'   => 0,
            'day'    => $diff->d,
            'hour'   => $diff->h,
            'minute' => $diff->i,
            'second' => $diff->s,
        );

        // DateInterval does not contain the number of weeks
        if ($with_week)
        {
            $chunks['week'] = (int)floor($chunks['day']/7);
            $chunks['day'] = $chunks['day'] - $chunks['week']*7;
        }

        $j = array_search($stop, array_keys($chunks));

        $print = ''; $i=0;
        foreach ($chunks as $name => $value)
        {
            if ($value != 0)
            {
                $print.= ' '.l10n_dec('%d '.$name, '%d '.$name.'s', $value);
            }
        
            if (!empty($print) && $i >= $j)
            {
                break;
            }
        
            $i++;
        }

        $print = trim($print);

        if ($with_text)
        {
            if ($diff->invert)
            {
                $print = l10n('%s ago', $print);
            }
            else
            {
                $print = l10n('%s in the future', $print);
            }
        }

        return $print;
    }

    /**
     * TODO
     *
     * this method nor its variant transform_date
     * are not used in the application
     */
    function transformDate($original, $format_in, $format_out, $default = null)
    {
        if (empty($original))
        {
            return $default;
        }

        $date = self::str2DateTime($original, $format_in);
        
        return $date->format($format_out);
    }
}
