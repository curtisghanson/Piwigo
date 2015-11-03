<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Piwigo\Image\Metadata\Exif;
use Piwigo\Image\Metadata\Itpc;

/**
 * This file is included by the picture page to manage picture metadata
 *
 */
if (($conf['show_exif']) and (function_exists('read_exif_data')))
{
    $exif_mapping = array();

    foreach ($conf['show_exif_fields'] as $field)
    {
        $exif_mapping[$field] = $field;
    }

    $exif = new Exif();
    $data = $exif->getData($picture['current']['src_image']->get_path(), $exif_mapping);

    if (count($data) > 0)
    {
        $tpl_meta = array(
            'TITLE' => l10n('EXIF Metadata'),
            'lines' => array(),
        );

        foreach ($conf['show_exif_fields'] as $field)
        {
            if (strpos($field, ';') === false)
            {
                if (isset($data[$field]))
                {
                    $key = $field;

                    if (isset($lang['exif_field_'.$field]))
                    {
                        $key = $lang['exif_field_'.$field];
                    }

                    $tpl_meta['lines'][$key] = $data[$field];
                }
            }
            else
            {
                $tokens = explode(';', $field);

                if (isset($data[$field]))
                {
                    $key = $tokens[1];

                    if (isset($lang['exif_field_'.$key]))
                    {
                        $key = $lang['exif_field_'.$key];
                    }

                    $tpl_meta['lines'][$key] = $data[$field];
                }
            }
        }

        $template->append('metadata', $tpl_meta);
    }
}

if ($conf['show_iptc'])
{
    $itpc = new Itpc();
    $data = $itpc->getData($picture['current']['src_image']->get_path(), $conf['show_iptc_mapping'], ', ');

    if (count($data) > 0)
    {
        $tpl_meta = array(
            'TITLE' => l10n('IPTC Metadata'),
            'lines' => array(),
        );

        foreach ($data as $field => $value)
        {
            $key = $field;

            if (isset($lang[$field]))
            {
                $key = $lang[$field];
            }

            $tpl_meta['lines'][$key] = $value;
        }

        $template->append('metadata', $tpl_meta);
    }
}
