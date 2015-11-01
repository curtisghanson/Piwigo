<?php

namespace Piwigo\Model;

/**
 * Base class for monthly and weekly calendar styles
 */
abstract class AbstractCalendar
{
    const C_YEAR  = 0;
    const C_WEEK  = 1;
    const C_MONTH = 1;
    const C_DAY   = 2;

    /** db column on which this calendar works */
    private $dateField;

    /** used for queries (INNER JOIN or normal) */
    private $innerSql;

    /** used to store db fields */
    private $calendarLevels;

    /**
     * Generate navigation bars for category page.
     *
     * @return boolean false indicates that thumbnails where not included
     */
    abstract function generateCategoryContent();

    /**
     * Returns a sql WHERE subquery for the date field.
     *
     * @param int $max_levels (e.g. 2=only year and month)
     * @return string
     */
    abstract function getDateWhere($maxLevels = 3);

    /**
     * Initialize the calendar.
     *
     * @param string $innerSql
     */
    public function initialize($innerSql)
    {
        global $page;

        if ($page['chronology_field'] == 'posted')
        {
            $this->dateField = 'date_available';
        }
        else
        {
            $this->dateField = 'date_creation';
        }

        $this->innerSql = $innerSql;
    }

    /**
     * Returns the calendar title (with HTML).
     *
     * @return string
     */
    public function getDisplayName()
    {
        global $conf;
        global $page;
        $res = '';

        for ($i = 0; $i < count($page['chronology_date']); $i++)
        {
            $res .= $conf['level_separator'];

            if ( isset($page['chronology_date'][$i+1]) )
            {
                $chronologyDate = array_slice($page['chronology_date'], 0, $i + 1);
                $url = duplicateIndexUrl(
                    array('chronology_date'=>$chronologyDate),
                    array('start' )
                );

                $res .=
                    '<a href="' . $url . '">'
                    . $this->getDateComponentLabel($i, $page['chronology_date'][$i])
                    . '</a>';
            }
            else
            {
                $res .=
                    '<span class="calInHere">'
                    . $this->getDateComponentLabel($i, $page['chronology_date'][$i])
                    . '</span>';
            }
        }

        return $res;
      }

    /**
     * Returns a display name for a date component optionally using labels.
     *
     * @return string
     */
    protected function getDateComponentLabel($level, $dateComponent)
    {
        $label = $dateComponent;

        if (isset($this->calendarLevels[$level]['labels'][$dateComponent]))
        {
            $label = $this->calendarLevels[$level]['labels'][$dateComponent];
        }
        else if ('any' === $dateComponent)
        {
            $label = l10n('All');
        }

        return $label;
    }

    /**
     * Gets a nice display name for a date to be shown in previous/next links
     *
     * @param string $date
     * @return string
     */
    protected function getDateNiceName($date)
    {
        $dateComponents = explode('-', $date);
        $res = '';

        for ($i = count($dateComponents) - 1; $i >= 0; $i--)
        {
            if ('any' !== $dateComponents[$i])
            {
                $label = $this->getDateComponentLabel($i, $dateComponents[$i] );

                if ( $res!='' )
                {
                    $res .= ' ';
                }

                $res .= $label;
            }
        }

        return $res;
    }

    /**
     * Creates a calendar navigation bar.
     *
     * @param array $dateComponents
     * @param array $items - hash of items to put in the bar (e.g. 2005,2006)
     * @param bool $showAny - adds any link to the end of the bar
     * @param bool $showEmpty - shows all labels even those without items
     * @param array $labels - optional labels for items (e.g. Jan,Feb,...)
     * @return string
     */
    protected function getNavBarFromItems($dateComponents, $items, $showAny, $showEmpty = false, $labels = null)
    {
        global $conf;
        global $page;
        global $template;

        $navBarDatas =array();

        if ($conf['calendar_show_empty'] and $showEmpty and !empty($labels))
        {
            foreach ($labels as $item => $label)
            {
                if (!isset($items[$item]))
                {
                    $items[$item] = -1;
                }
            }

            ksort($items);
        }

        foreach ($items as $item => $nbImages)
        {
            $label = $item;

            if (isset($labels[$item]))
            {
                $label = $labels[$item];
            }

            if ($nbImages == -1)
            {
                $tmpDatas = array('LABEL'=> $label);
            }
            else
            {
                $url = duplicate_index_url(
                    array('chronology_date' => array_merge($dateComponents,array($item))),
                    array('start')
                );

                $tmpDatas = array(
                    'LABEL'=> $label,
                    'URL'  => $url
                );
            }

            if ($nbImages > 0)
            {
                $tmpDatas['NB_IMAGES'] = $nbImages;
            }

            $navBarDatas[] = $tmpDatas;
        }

        if ($conf['calendar_show_any'] and $showAny and count($items)>1 and count($dateComponents)<count($this->calendarLevels)-1 )
        {
            $url = duplicate_index_url(
                array('chronology_date' => array_merge($dateComponents, array('any'))),
                array( 'start' )
            );

            $navBarDatas[]=array(
                'LABEL' => l10n('All'),
                'URL'   => $url
            );
        }

        return $navBarDatas;
    }

    /**
     * Creates a calendar navigation bar for a given level.
     *
     * @param int $level - 0-year, 1-month/week, 2-day
     */
    protected function buildNavBar($level, $labels = null)
    {
        global $template;
        global $conf;
        global $page;

        $query = '
SELECT DISTINCT('.$this->calendarLevels[$level]['sql'].') as period,
  COUNT(DISTINCT id) as nb_images'.
$this->innerSql.
$this->getDateWhere($level).'
  GROUP BY period;';

        $levelItems = query2array($query, 'period', 'nb_images');

        if ( count($levelItems)==1 and count($page['chronology_date'])<count($this->calendarLevels)-1)
        {
            if ( ! isset($page['chronology_date'][$level]) )
            {
                list($key) = array_keys($levelItems);
                $page['chronology_date'][$level] = (int)$key;

                if ( $level<count($page['chronology_date']) and $level!=count($this->calendarLevels)-1 )
                {
                    return;
                }
            }
        }

        $dates = $page['chronology_date'];
        while ($level<count($dates))
        {
            array_pop($dates);
        }

        $nav_bar = $this->getNavBarFromItems(
            $dates,
            $levelItems,
            true,
            true,
            isset($labels) ? $labels : $this->calendarLevels[$level]['labels']
        );

        $template->append(
            'chronology_navigation_bars',
            array('items' => $nav_bar)
        );
    }

    /**
     * Assigns the next/previous link to the template with regards to
     * the currently choosen date.
     */
    protected function buildNextPrev()
    {
        global $template;
        global $page;

        $prev = null;
        $next = null;

        if (empty($page['chronology_date']))
        {
            return;
        }

        $subQueries = array();
        $nbElements = count($page['chronology_date']);

        for ($i = 0; $i < $nbElements; $i++)
        {
            if ('any' === $page['chronology_date'][$i])
            {
                $subQueries[] = '\'any\'';
            }
            else
            {
                $subQueries[] = pwg_db_cast_to_text($this->calendarLevels[$i]['sql']);
            }
        }

        $query = 'SELECT '.pwg_db_concat_ws($subQueries, '-').' AS period';
        $query .= $this->innerSql .'
AND ' . $this->dateField . ' IS NOT NULL
GROUP BY period';

        $current    = implode('-', $page['chronology_date'] );
        $upperItems = query2array($query,null, 'period');

        usort($upperItems, 'version_compare');
        $upperItemsRank = array_flip($upperItems);

        if (!isset($upperItemsRank[$current]))
        {
            $upperItems[] = $current;// just in case (external link)
            usort($upperItems, 'version_compare');
            $upperItemsRank = array_flip($upperItems);
        }

        $currentRank = $upperItemsRank[$current];
        $tplVar      = array();

        if ($currentRank > 0)
        {
            // has previous
            $prev = $upperItems[$currentRank-1];
            $chronologyDate = explode('-', $prev);
            $tplVar['previous'] = array(
                'LABEL' => $this->getDateNiceName($prev),
                'URL'   => duplicate_index_url(array('chronology_date'=>$chronologyDate), array('start'))
            );
        }

        if ( $currentRank < count($upperItems)-1 )
        {
            // has next
            $next = $upperItems[$currentRank+1];
            $chronologyDate = explode('-', $next);
            $tplVar['next'] = array(
                'LABEL' => $this->getDateNiceName($next),
                'URL'   => duplicate_index_url(array('chronology_date'=>$chronologyDate), array('start'))
            );
        }

        if ( !empty($tplVar) )
        {
            $existing = $template->smarty->getVariable('chronology_navigation_bars');
            if (! ($existing instanceof Undefined_Smarty_Variable))
            {
                $existing->value[ sizeof($existing->value)-1 ] =
                    array_merge( $existing->value[ sizeof($existing->value)-1 ], $tplVar);
            }
            else
            {
                $template->append( 'chronology_navigation_bars', $tplVar );
            }
        }
    }
}
