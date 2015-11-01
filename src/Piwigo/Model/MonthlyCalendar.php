<?php
namespace Piwigo\Model;

use Piwigo\Model\AbstractCalendar;

/**
 * Monthly calendar style (composed of years/months and days)
 */
class CalendarMonthly extends AbstractCalendar
{
    /**
     * Initialize the calendar.
     * @param string $innerSql
     */
    function initialize($innerSql)
    {
        parent::initialize($innerSql);

        global $lang;
        $this->calendarLevels = array(
            array(
                'sql'    => pwg_db_get_year($this->dateField),
                'labels' => null
            ),
            array(
                'sql'    => pwg_db_get_month($this->dateField),
                'labels' => $lang['month']
            ),
            array(
                'sql'    => pwg_db_get_dayofmonth($this->dateField),
                'labels' => null
            ),
        );
    }

    /**
     * Generate navigation bars for category page.
     *
     * @return boolean false indicates that thumbnails where not included
     */
    function generateCategoryContent()
    {
        global $conf;
        global $page;

        $viewType = $page['chronology_view'];

        if ($viewType == CAL_VIEW_CALENDAR)
        {
            global $template;

            $tplVar = array();
            if (count($page['chronology_date']) == 0)
            {
                //case A: no year given - display all years+months
                if ($this->buildGlobalCalendar($tplVar))
                {
                    $template->assign('chronology_calendar', $tplVar);
                    return true;
                }
            }

            if ( count($page['chronology_date'])== 1 )
            {
                //case B: year given - display all days in given year
                if ($this->buildYearCalendar($tplVar))
                {
                    $template->assign('chronology_calendar', $tplVar);
                    $this->buildNavBar(parent::C_YEAR); // years
                    return true;
                }
            }

            if ( count($page['chronology_date'])== 2 )
            {
                //case C: year+month given - display a nice month calendar
                if ($this->buildMonthCalendar($tplVar))
                {
                    $template->assign('chronology_calendar', $tplVar);
                }
                $this->buildNextPrev();
                return true;
            }
        }

        if ($viewType == CAL_VIEW_LIST or count($page['chronology_date'])== 3)
        {
            if ( count($page['chronology_date'])== 0 )
            {
                $this->buildNavBar(parent::C_YEAR); // years
            }

            if ( count($page['chronology_date'])== 1)
            {
                $this->buildNavBar(parent::C_MONTH); // month
            }

            if ( count($page['chronology_date'])== 2 )
            {
                $dayLabels = range(1, $this->getAllDaysInMonth(
                                $page['chronology_date'][parent::C_YEAR], $page['chronology_date'][parent::C_MONTH]));
                array_unshift($dayLabels, 0);
                unset($dayLabels[0]);
                $this->buildNavBar(parent::C_DAY, $dayLabels); // days
            }

            $this->buildNextPrev();
        }

        return false;
    }

    /**
     * Returns a sql WHERE subquery for the date field.
     *
     * @param int $maxLevels (e.g. 2 = only year and month)
     * @return string
     */
    function getDateWhere($maxLevels = 3)
    {
        global $page;

        $date = $page['chronology_date'];
        while (count($date) > $maxLevels)
        {
            array_pop($date);
        }

        $res = '';
        if (isset($date[parent::C_YEAR]) and $date[parent::C_YEAR] !== 'any')
        {
            $b = $date[parent::C_YEAR] . '-';
            $e = $date[parent::C_YEAR] . '-';

            if (isset($date[parent::C_MONTH]) and $date[parent::C_MONTH]!=='any')
            {
                $b .= sprintf('%02d-', $date[parent::C_MONTH]);
                $e .= sprintf('%02d-', $date[parent::C_MONTH]);
                if (isset($date[parent::C_DAY]) and $date[parent::C_DAY]!=='any')
                {
                    $b .= sprintf('%02d', $date[parent::C_DAY]);
                    $e .= sprintf('%02d', $date[parent::C_DAY]);
                }
                else
                {
                    $b .= '01';
                    $e .= $this->getAllDaysInMonth($date[parent::C_YEAR], $date[parent::C_MONTH]);
                }
            }
            else
            {
                $b .= '01-01';
                $e .= '12-31';
                if (isset($date[parent::C_MONTH]) and $date[parent::C_MONTH]!=='any')
                {
                    $res .= ' AND '.$this->calendarLevels[parent::C_MONTH]['sql'].'='.$date[parent::C_MONTH];
                }
                if (isset($date[parent::C_DAY]) and $date[parent::C_DAY]!=='any')
                {
                    $res .= ' AND '.$this->calendarLevels[parent::C_DAY]['sql'].'='.$date[parent::C_DAY];
                }
            }
            $res = " AND $this->dateField BETWEEN '$b' AND '$e 23:59:59'" . $res;
        }
        else
        {
            $res = ' AND '.$this->dateField.' IS NOT NULL';
            if (isset($date[parent::C_MONTH]) and $date[parent::C_MONTH]!=='any')
            {
                $res .= ' AND '.$this->calendarLevels[parent::C_MONTH]['sql'].'='.$date[parent::C_MONTH];
            }
            if (isset($date[parent::C_DAY]) and $date[parent::C_DAY]!=='any')
            {
                $res .= ' AND '.$this->calendarLevels[parent::C_DAY]['sql'].'='.$date[parent::C_DAY];
            }
        }
        return $res;
    }

    /**
     * Returns an array with all the days in a given month.
     *
     * @param int $year
     * @param int $month
     * @return int[]
     */
    protected function getAllDaysInMonth($year, $month)
    {
        $md = array(1 =>31,28,31,30,31,30,31,31,30,31,30,31);

        if (is_numeric($year) and $month == 2)
        {
            $nbDays = $md[2];
            if (($year % 4 == 0) and (($year % 100!= 0) or ($year % 400!= 0)))
            {
                $nbDays++;
            }
        }
        elseif (is_numeric($month))
        {
            $nbDays = $md[$month];
        }
        else
        {
            $nbDays = 31;
        }

        return $nbDays;
    }

    /**
     * Build global calendar and assign the result in _$tplVar_
     *
     * @param array $tplVar
     * @return bool
     */
    protected function buildGlobalCalendar(&$tplVar)
    {
        global $page;

        assert( count($page['chronology_date']) == 0 );
        $query ='
    SELECT '.pwg_db_get_date_YYYYMM($this->dateField).' as period,
        COUNT(distinct id) as count';
        $query.= $this->innerSql;
        $query.= $this->getDateWhere();
        $query.= '
        GROUP BY period
        ORDER BY '.pwg_db_get_year($this->dateField).' DESC, '.pwg_db_get_month($this->dateField).' ASC';

        $result = pwg_query($query);
        $items = array();
        while ($row = pwg_db_fetch_assoc($result))
        {
            $y = substr($row['period'], 0, 4);
            $m = (int) substr($row['period'], 4, 2);

            if ( ! isset($items[$y]) )
            {
                $items[$y] = array('nb_images'=>0, 'children'=>array() );
            }

            $items[$y]['children'][$m] = $row['count'];
            $items[$y]['nb_images'] += $row['count'];
        }

        //echo ('<pre>'. var_export($items, true) . '</pre>');
        if (count($items) == 1)
        {
            // only one year exists so bail out to year view
            list($y) = array_keys($items);
            $page['chronology_date'][parent::C_YEAR] = $y;

            return false;
        }

        global $lang;
        foreach ($items as $year => $yearData)
        {
            $chronologyDate = array($year);
            $url = duplicate_index_url(array('chronology_date'=>$chronologyDate));

            $navBar = $this->getNavBarFromItems( $chronologyDate,
                            $yearData['children'], false, false, $lang['month'] );

            $tplVar['calendar_bars'][] =
                array(
                    'U_HEAD'  => $url,
                    'NB_IMAGES' => $yearData['nb_images'],
                    'HEAD_LABEL' => $year,
                    'items' => $navBar,
                );
        }

        return true;
    }

    /**
     * Build year calendar and assign the result in _$tplVar_
     *
     * @param array $tplVar
     * @return bool
     */
    protected function buildYearCalendar(&$tplVar)
    {
        global $page;

        assert( count($page['chronology_date']) == 1 );
        $query ='SELECT '.pwg_db_get_date_MMDD($this->dateField).' as period,
                            COUNT(DISTINCT id) as count';
        $query .= $this->innerSql;
        $query .= $this->getDateWhere();
        $query .= '
        GROUP BY period
        ORDER BY period ASC';

        $result = pwg_query($query);
        $items  = array();
        while ($row = pwg_db_fetch_assoc($result))
        {
            $m = (int)substr($row['period'], 0, 2);
            $d = substr($row['period'], 2, 2);
            if ( ! isset($items[$m]) )
            {
                $items[$m] = array('nb_images'=>0, 'children'=>array() );
            }
            $items[$m]['children'][$d] = $row['count'];
            $items[$m]['nb_images'] += $row['count'];
        }
        if (count($items)== 1)
        { // only one month exists so bail out to month view
            list($m) = array_keys($items);
            $page['chronology_date'][parent::C_MONTH] = $m;
            return false;
        }
        global $lang;
        foreach ( $items as $month =>$month_data)
        {
            $chronologyDate = array( $page['chronology_date'][parent::C_YEAR], $month );
            $url = duplicate_index_url(array('chronology_date'=>$chronologyDate));

            $navBar = $this->getNavBarFromItems( $chronologyDate,
                                             $month_data['children'], false );

            $tplVar['calendar_bars'][] =
                array(
                    'U_HEAD'  => $url,
                    'NB_IMAGES' => $month_data['nb_images'],
                    'HEAD_LABEL' => $lang['month'][$month],
                    'items' => $navBar,
                );
        }

        return true;
    }

    /**
     * Build month calendar and assign the result in _$tplVar_
     *
     * @param array $tplVar
     * @return bool
     */
    protected function buildMonthCalendar(&$tplVar)
    {
        global $page, $lang, $conf;

        $query ='SELECT ' . pwg_db_get_dayofmonth($this->dateField) . ' as period,
                            COUNT(DISTINCT id) as count';
        $query.= $this->innerSql;
        $query.= $this->getDateWhere();
        $query.= '
        GROUP BY period
        ORDER BY period ASC';

        $items  = array();
        $result = pwg_query($query);
        while ($row = pwg_db_fetch_assoc($result))
        {
            $d         = (int) $row['period'];
            $items[$d] = array('nb_images' => $row['count']);
        }

        foreach ($items as $day => $data)
        {
            $page['chronology_date'][parent::C_DAY] = $day;
            $query = '
    SELECT id, file,representative_ext,path,width,height,rotation, '.pwg_db_get_dayofweek($this->dateField) . '-1 as dow';
            $query.= $this->innerSql;
            $query.= $this->getDateWhere();
            $query.= '
        ORDER BY ' . DB_RANDOM_FUNCTION . '()
        LIMIT 1';
            unset ($page['chronology_date'][parent::C_DAY]);

            $row        = pwg_db_fetch_assoc(pwg_query($query));
            $derivative = new DerivativeImage(IMG_SQUARE, new SrcImage($row));
            $items[$day]['derivative'] = $derivative;
            $items[$day]['file']       = $row['file'];
            $items[$day]['dow']        = $row['dow'];
        }

        if (!empty($items))
        {
            list($knownDay) = array_keys($items);
            $knownDow       = $items[$knownDay]['dow'];
            $firstDayDow    = ($knownDow - ($knownDay - 1)) % 7;

            if ($firstDayDow < 0)
            {
                $firstDayDow += 7;
            }

            //firstDayDow = week day corresponding to the first day of this month
            $wdayLabels = $lang['day'];

            if ('monday' == $conf['week_starts_on'])
            {
                if ($firstDayDow == 0)
                {
                    $firstDayDow = 6;
                }
                else
                {
                    $firstDayDow -= 1;
                }

                $wdayLabels[] = array_shift($wdayLabels);
            }

            list($cellWidth, $cellHeight) = ImageStdParams::get_by_type(IMG_SQUARE)->sizing->ideal_size;

            $tplWeeks   = array();
            $tplCrtWeek = array();

            //fill the empty days in the week before first day of this month
            for ($i = 0; $i < $firstDayDow; $i++)
            {
                $tplCrtWeek[] = array();
            }

            for ($day = 1;
                        $day <= $this->getAllDaysInMonth(
                            $page['chronology_date'][parent::C_YEAR], $page['chronology_date'][parent::C_MONTH]
                                );
                        $day++)
            {
                $dow = ($firstDayDow + $day-1)%7;
                if ($dow == 0 and $day != 1)
                {
                    $tplWeeks[] = $tplCrtWeek; // add finished week to week list
                    $tplCrtWeek = array(); // start new week
                }

                if (!isset($items[$day]))
                {
                    // empty day
                    $tplCrtWeek[] = array('DAY' => $day);
                }
                else
                {
                    $url = duplicate_index_url(array(
                        'chronology_date' => array(
                            $page['chronology_date'][parent::C_YEAR],
                            $page['chronology_date'][parent::C_MONTH],
                            $day
                        )
                    ));

                    $tplCrtWeek[] = array(
                        'DAY'         => $day,
                        'DOW'         => $dow,
                        'NB_ELEMENTS' => $items[$day]['nb_images'],
                        'IMAGE'       => $items[$day]['derivative']->get_url(),
                        'U_IMG_LINK'  => $url,
                        'IMAGE_ALT'   => $items[$day]['file'],
                    );
                }
            }
            //fill the empty days in the week after the last day of this month
            while ($dow < 6)
            {
                $tplCrtWeek[] = array();
                $dow++;
            }

            $tplWeeks[] = $tplCrtWeek;

            $tplVar['month_view'] = array(
                'CELL_WIDTH'  => $cellWidth,
                'CELL_HEIGHT' => $cellHeight,
                'wday_labels' => $wdayLabels,
                'weeks'       => $tplWeeks,
            );
        }

        return true;
    }
}