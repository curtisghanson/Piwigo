<?php

namespace Piwigo\Model;

use Piwigo\Model\AbstractCalendar;

/**
 * Weekly calendar style (composed of years/week in years and days in week)
 */
class WeeklyCalendar extends AbstractCalendar
{
    /**
     * Initialize the calendar
     * @param string $innerSql
     */
    public function initialize($innerSql)
    {
        parent::initialize($innerSql);

        global $lang;
        global $conf;

        $weekNoLabels = array();

        for ($i = 1; $i <= 53; $i++)
        {
            $weekNoLabels[$i] = l10n('Week %d', $i);
            //$weekNoLabels[$i] = $i;
        }

        $this->calendarLevels = array(
            array(
                'sql'    => pwg_db_get_year($this->dateField),
                'labels' => null
            ),
            array(
                'sql'    => pwg_db_get_week($this->dateField).'+1',
                'labels' => $weekNoLabels,
            ),
            array(
                'sql'    => pwg_db_get_dayofweek($this->dateField).'-1',
                'labels' => $lang['day']
            ),
        );

        //Comment next lines for week starting on Sunday or if MySQL version<4.0.17
        //WEEK(date,5) = "0-53 - Week 1=the first week with a Monday in this year"
        if ('monday' == $conf['week_starts_on'])
        {
            $this->calendarLevels[parent::C_WEEK]['sql']     = pwg_db_get_week($this->dateField, 5) . '+1';
            $this->calendarLevels[parent::C_DAY]['sql']      = pwg_db_get_weekday($this->dateField);
            $this->calendarLevels[parent::C_DAY]['labels'][] = array_shift($this->calendarLevels[parent::C_DAY]['labels']);
        }
    }

    /**
     * Generate navigation bars for category page.
     *
     * @return boolean false indicates that thumbnails where not included
     */
    public function generateCategoryContent()
    {
        global $conf;
        global $page;

        if (count($page['chronology_date']) == 0)
        {
            $this->buildNavBar(parent::C_YEAR); // years
        }

        if (count($page['chronology_date']) == 1)
        {
            $this->buildNavBar(parent::C_WEEK, array()); // week nav bar 1-53
        }

        if (count($page['chronology_date']) == 2)
        {
            $this->buildNavBar(parent::C_DAY); // days nav bar Mon-Sun
        }

        $this->buildNextPrev();

        return false;
    }

    /**
     * Returns a sql WHERE subquery for the date field.
     *
     * @param int $maxLevels (e.g. 2=only year and month)
     * @return string
     */
    public function getDateWhere($maxLevels = 3)
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
            $y   = $date[parent::C_YEAR];
            $res = " AND $this->dateField BETWEEN '$y-01-01' AND '$y-12-31 23:59:59'";
        }

        if (isset($date[parent::C_WEEK]) and $date[parent::C_WEEK]!=='any')
        {
            $res .= ' AND ' . $this->calendarLevels[parent::C_WEEK]['sql'] . '=' . $date[parent::C_WEEK];
        }

        if (isset($date[parent::C_DAY]) and $date[parent::C_DAY]!=='any')
        {
            $res .= ' AND ' . $this->calendarLevels[parent::C_DAY]['sql'] . '=' . $date[parent::C_DAY];
        }

        if (empty($res))
        {
            $res = ' AND ' . $this->dateField . ' IS NOT NULL';
        }

        return $res;
    }
}
