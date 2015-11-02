<?php
require_once __DIR__ . '/../vendor/autoload.php';

/**
 * @package functions\filter
 */


/**
 * Updates data of categories with filtered values
 *
 * @param array &$cats
 */
function update_cats_with_filtered_data(&$cats)
{
  global $filter;

  if ($filter['enabled'])
  {
    $upd_fields = array('date_last', 'max_date_last', 'count_images', 'count_categories', 'nb_images');

    foreach ($cats as $cat_id => $category)
    {
      foreach ($upd_fields as $upd_field)
      {
        $cats[$cat_id][$upd_field] = $filter['categories'][$category['id']][$upd_field];
      }
    }
  }
}

?>