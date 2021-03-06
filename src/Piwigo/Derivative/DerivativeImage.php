<?php
namespace Piwigo\Derivative;

use Piwigo\Derivative\SrcImage;
use Piwigo\Derivative\ImageStdParams;
use Piwigo\Derivative\DerivativeImage;

/**
 * Holds information (path, url, dimensions) about a derivative image.
 * A derivative image is constructed from a source image (SrcImage class)
 * and derivative parameters (DerivativeParams class).
 */
final class DerivativeImage
{
  /** @var SrcImage */
  public $src_image;
  /** @var array */
  private $params;
  /** @var string */
  private $rel_path;
  /** @var string */
  private $rel_url;
  /** @var bool */
  private $is_cached=true;

  /**
   * @param string|DerivativeParams $type standard derivative param type (e.g. IMG_*)
   *    or a DerivativeParams object
   * @param SrcImage $src_image the source image of this derivative
   */
  function __construct($type, SrcImage $src_image)
  {
    $this->src_image = $src_image;
    if (is_string($type))
    {
      $this->params = ImageStdParams::get_by_type($type);
    }
    else
    {
      $this->params = $type;
    }

    self::build($src_image, $this->params, $this->rel_path, $this->rel_url, $this->is_cached);
  }

  /**
   * Generates the url of a thumbnail.
   *
   * @param array|SrcImage $infos array of info from db or SrcImage
   * @return string
   */
  static function thumb_url($infos)
  {
    return self::url(IMG_THUMB, $infos);
  }

  /**
   * Generates the url for a particular photo size.
   *
   * @param string|DerivativeParams $type standard derivative param type (e.g. IMG_*)
   *    or a DerivativeParams object
   * @param array|SrcImage $infos array of info from db or SrcImage
   * @return string
   */
  static function url($type, $infos)
  {
    $src_image = is_object($infos) ? $infos : new SrcImage($infos);
    $params = is_string($type) ? ImageStdParams::get_by_type($type) : $type;
    self::build($src_image, $params, $rel_path, $rel_url);
    if ($params == null)
    {
      return $src_image->get_url();
    }
    return embellish_url(
        trigger_change('get_derivative_url',
          get_root_url().$rel_url,
          $params, $src_image, $rel_url
          ) );
  }

  /**
   * Return associative an array of all DerivativeImage for a specific image.
   * Disabled derivative types can be still found in the return, mapped to an
   * enabled derivative (e.g. the values are not unique in the return array).
   * This is useful for any plugin/theme to just use $deriv[IMG_XLARGE] even if
   * the XLARGE is disabled.
   *
   * @param array|SrcImage $src_image array of info from db or SrcImage
   * @return DerivativeImage[]
   */
  static function get_all($src_image)
  {
    if (!is_object($src_image))
    {
      $src_image = new SrcImage($src_image);
    }

    $ret = array();
    // build enabled types
    foreach (ImageStdParams::get_defined_type_map() as $type => $params)
    {
      $derivative = new DerivativeImage($params, $src_image);
      $ret[$type] = $derivative;
    }
    // disabled types, fallback to enabled types
    foreach (ImageStdParams::get_undefined_type_map() as $type => $type2)
    {
      $ret[$type] = $ret[$type2];
    }

    return $ret;
  }

  /**
   * Returns an instance of DerivativeImage for a specific image and size.
   * Disabled derivatives fallback to an enabled derivative.
   *
   * @param string $type standard derivative param type (e.g. IMG_*)
   * @param array|SrcImage $src_image array of info from db or SrcImage
   * @return DerivativeImage|null null if $type not found
   */
  static function get_one($type, $src_image)
  {
    if (!is_object($src_image))
    {
      $src_image = new SrcImage($src_image);
    }

    $defined = ImageStdParams::get_defined_type_map();
    if (isset($defined[$type]))
    {
      return new DerivativeImage($defined[$type], $src_image);
    }

    $undefined = ImageStdParams::get_undefined_type_map();
    if (isset($undefined[$type]))
    {
      return new DerivativeImage($defined[ $undefined[$type] ], $src_image);
    }

    return null;
  }

  /**
   * @todo : documentation of DerivativeImage::build
   */
  private static function build($src, &$params, &$rel_path, &$rel_url, &$is_cached=null)
  {
    if ( $src->has_size() && $params->is_identity($src->get_size() ))
    {// the source image is smaller than what we should do - we do not upsample
      if (!$params->will_watermark($src->get_size()) && !$src->rotation)
      {// no watermark, no rotation required -> we will use the source image
        $params = null;
        $rel_path = $rel_url = $src->rel_path;
        return;
      }
      $defined_types = array_keys(ImageStdParams::get_defined_type_map());
      for ($i=0; $i<count($defined_types); $i++)
      {
        if ($defined_types[$i] == $params->type)
        {
          for ($i--; $i>=0; $i--)
          {
            $smaller = ImageStdParams::get_by_type($defined_types[$i]);
            if ($smaller->sizing->max_crop==$params->sizing->max_crop && $smaller->is_identity($src->get_size()))
            {
              $params = $smaller;
              self::build($src, $params, $rel_path, $rel_url, $is_cached);
              return;
            }
          }
          break;
        }
      }
    }

    $tokens=array();
    $tokens[] = substr($params->type,0,2);

    if ($params->type==IMG_CUSTOM)
    {
      $params->add_url_tokens($tokens);
    }

    $loc = $src->rel_path;
    if (substr_compare($loc, './', 0, 2)==0)
    {
      $loc = substr($loc, 2);
    }
    elseif (substr_compare($loc, '../', 0, 3)==0)
    {
      $loc = substr($loc, 3);
    }
    $loc = substr_replace($loc, '-'.implode('_', $tokens), strrpos($loc, '.'), 0 );

    $rel_path = PWG_DERIVATIVE_DIR.$loc;

    global $conf;
    $url_style=$conf['derivative_url_style'];
    if (!$url_style)
    {
      $mtime = @filemtime(PHPWG_ROOT_PATH.$rel_path);
      if ($mtime===false or $mtime < $params->last_mod_time)
      {
        $is_cached = false;
        $url_style = 2;
      }
      else
      {
        $url_style = 1;
      }
    }

    if ($url_style == 2)
    {
      $rel_url = 'i';
      if ($conf['php_extension_in_urls']) $rel_url .= '.php';
      if ($conf['question_mark_in_urls']) $rel_url .= '?';
      $rel_url .= '/'.$loc;
    }
    else
    {
      $rel_url = $rel_path;
    }
  }

  /**
   * @return string
   */
  function get_path()
  {
    return PHPWG_ROOT_PATH.$this->rel_path;
  }

  /**
   * @return string
   */
  function get_url()
  {
    if ($this->params == null)
    {
      return $this->src_image->get_url();
    }
    return embellish_url(
        trigger_change('get_derivative_url',
          get_root_url().$this->rel_url,
          $this->params, $this->src_image, $this->rel_url
          ) );
  }

  /**
   * @return bool
   */
  function same_as_source()
  {
    return $this->params == null;
  }

  /**
   * @return string one if IMG_* or 'Original'
   */
  function get_type()
  {
    if ($this->params == null)
      return 'Original';
    return $this->params->type;
  }

  /**
   * @return int[]
   */
  function get_size()
  {
    if ($this->params == null)
    {
      return $this->src_image->get_size();
    }
    return $this->params->compute_final_size($this->src_image->get_size());
  }

  /**
   * Returns the size as CSS rule.
   *
   * @return string
   */
  function get_size_css()
  {
    $size = $this->get_size();
    if ($size)
    {
      return 'width:'.$size[0].'px; height:'.$size[1].'px';
    }
  }

  /**
   * Returns the size as HTML attributes.
   *
   * @return string
   */
  function get_size_htm()
  {
    $size = $this->get_size();
    if ($size)
    {
      return 'width="'.$size[0].'" height="'.$size[1].'"';
    }
  }

  /**
   * Returns literal size: $widthx$height.
   *
   * @return string
   */
  function get_size_hr()
  {
    $size = $this->get_size();
    if ($size)
    {
      return $size[0].' x '.$size[1];
    }
  }

  /**
   * @param int $maxw
   * @param int $mawh
   * @return int[]
   */
  function get_scaled_size($maxw, $maxh)
  {
    $size = $this->get_size();
    if ($size)
    {
      $ratio_w = $size[0] / $maxw;
      $ratio_h = $size[1] / $maxh;
      if ($ratio_w>1 || $ratio_h>1)
      {
        if ($ratio_w > $ratio_h)
        {
          $size[0] = $maxw;
          $size[1] = floor($size[1] / $ratio_w);
        }
        else
        {
          $size[0] = floor($size[0] / $ratio_h);
          $size[1] = $maxh;
        }
      }
    }
    return $size;
  }

  /**
   * Returns the scaled size as HTML attributes.
   *
   * @param int $maxw
   * @param int $mawh
   * @return string
   */
  function get_scaled_size_htm($maxw=9999, $maxh=9999)
  {
    $size = $this->get_scaled_size($maxw, $maxh);
    if ($size)
    {
      return 'width="'.$size[0].'" height="'.$size[1].'"';
    }
  }

  /**
   * @return bool
   */
  function is_cached()
  {
    return $this->is_cached;
  }
}