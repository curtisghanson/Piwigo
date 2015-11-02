<?php
require_once __DIR__ . '/../vendor/autoload.php';

use \Imagick;
use \ImagickPixel;
use Piwigo\Image\ImageInterface;

// +-----------------------------------------------------------------------+
// |                   Class for Imagick extension                         |
// +-----------------------------------------------------------------------+

//class image_imagick implements imageInterface
class ImagickImage implements ImageInterface
{
  var $image;

  function __construct($source_filepath)
  {
    // A bug cause that Imagick class can not be extended
    $this->image = new Imagick($source_filepath);
  }

  function get_width()
  {
    return $this->image->getImageWidth();
  }

  function get_height()
  {
    return $this->image->getImageHeight();
  }

  function set_compression_quality($quality)
  {
    return $this->image->setImageCompressionQuality($quality);
  }

  function crop($width, $height, $x, $y)
  {
    return $this->image->cropImage($width, $height, $x, $y);
  }

  function strip()
  {
    return $this->image->stripImage();
  }

  function rotate($rotation)
  {
    $this->image->rotateImage(new ImagickPixel(), -$rotation);
    $this->image->setImageOrientation(Imagick::ORIENTATION_TOPLEFT);
    return true;
  }

  function resize($width, $height)
  {
    $this->image->setInterlaceScheme(Imagick::INTERLACE_LINE);

    // TODO need to explain this condition
    if ($this->get_width()%2 == 0
        && $this->get_height()%2 == 0
        && $this->get_width() > 3*$width)
    {
      $this->image->scaleImage($this->get_width()/2, $this->get_height()/2);
    }

    return $this->image->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 0.9);
  }

  function sharpen($amount)
  {
    $m = pwg_image::get_sharpen_matrix($amount);
    return  $this->image->convolveImage($m);
  }

  function compose($overlay, $x, $y, $opacity)
  {
    $ioverlay = $overlay->image->image;
    /*if ($ioverlay->getImageAlphaChannel() !== Imagick::ALPHACHANNEL_OPAQUE)
    {
      // Force the image to have an alpha channel
      $ioverlay->setImageAlphaChannel(Imagick::ALPHACHANNEL_OPAQUE);
    }*/

    global $dirty_trick_xrepeat;
    if ( !isset($dirty_trick_xrepeat) && $opacity < 100)
    {// NOTE: Using setImageOpacity will destroy current alpha channels!
      $ioverlay->evaluateImage(Imagick::EVALUATE_MULTIPLY, $opacity / 100, Imagick::CHANNEL_ALPHA);
      $dirty_trick_xrepeat = true;
    }

    return $this->image->compositeImage($ioverlay, Imagick::COMPOSITE_DISSOLVE, $x, $y);
  }

  function write($destination_filepath)
  {
    // use 4:2:2 chroma subsampling (reduce file size by 20-30% with "almost" no human perception)
    $this->image->setSamplingFactors( array(2,1) );
    return $this->image->writeImage($destination_filepath);
  }
}

// +-----------------------------------------------------------------------+
// |            Class for ImageMagick external installation                |
// +-----------------------------------------------------------------------+

class ExternalImagickImage implements ImageInterface
{
  var $imagickdir = '';
  var $source_filepath = '';
  var $width = '';
  var $height = '';
  var $commands = array();

  function __construct($source_filepath)
  {
    global $conf;
    $this->source_filepath = $source_filepath;
    $this->imagickdir = $conf['ext_imagick_dir'];

    if (strpos(@$_SERVER['SCRIPT_FILENAME'], '/kunden/') === 0)  // 1and1
    {
      @putenv('MAGICK_THREAD_LIMIT=1');
    }

    $command = $this->imagickdir.'identify -format "%wx%h" "'.realpath($source_filepath).'"';
    @exec($command, $returnarray);
    if(!is_array($returnarray) or empty($returnarray[0]) or !preg_match('/^(\d+)x(\d+)$/', $returnarray[0], $match))
    {
      die("[External ImageMagick] Corrupt image\n" . var_export($returnarray, true));
    }

    $this->width = $match[1];
    $this->height = $match[2];
  }

  function add_command($command, $params=null)
  {
    $this->commands[$command] = $params;
  }

  function get_width()
  {
    return $this->width;
  }

  function get_height()
  {
    return $this->height;
  }

  function crop($width, $height, $x, $y)
  {
    $this->width = $width;
    $this->height = $height;

    $this->add_command('crop', $width.'x'.$height.'+'.$x.'+'.$y);
    return true;
  }

  function strip()
  {
    $this->add_command('strip');
    return true;
  }

  function rotate($rotation)
  {
    if (empty($rotation))
    {
      return true;
    }

    if ($rotation==90 || $rotation==270)
    {
      $tmp = $this->width;
      $this->width = $this->height;
      $this->height = $tmp;
    }
    $this->add_command('rotate', -$rotation);
    $this->add_command('orient', 'top-left');
    return true;
  }

  function set_compression_quality($quality)
  {
    $this->add_command('quality', $quality);
    return true;
  }

  function resize($width, $height)
  {
    $this->width = $width;
    $this->height = $height;

    $this->add_command('filter', 'Lanczos');
    $this->add_command('resize', $width.'x'.$height.'!');
    return true;
  }

  function sharpen($amount)
  {
    $m = pwg_image::get_sharpen_matrix($amount);

    $param ='convolve "'.count($m).':';
    foreach ($m as $line)
    {
      $param .= ' ';
      $param .= implode(',', $line);
    }
    $param .= '"';
    $this->add_command('morphology', $param);
    return true;
  }

  function compose($overlay, $x, $y, $opacity)
  {
    $param = 'compose dissolve -define compose:args='.$opacity;
    $param .= ' '.escapeshellarg(realpath($overlay->image->source_filepath));
    $param .= ' -gravity NorthWest -geometry +'.$x.'+'.$y;
    $param .= ' -composite';
    $this->add_command($param);
    return true;
  }

  function write($destination_filepath)
  {
    $this->add_command('interlace', 'line'); // progressive rendering
    // use 4:2:2 chroma subsampling (reduce file size by 20-30% with "almost" no human perception)
    //
    // option deactivated for Piwigo 2.4.1, it doesn't work fo old versions
    // of ImageMagick, see bug:2672. To reactivate once we have a better way
    // to detect IM version and when we know which version supports this
    // option
    //
    if (version_compare(Image::$ext_imagick_version, '6.6') > 0)
    {
      $this->add_command('sampling-factor', '4:2:2' );
    }

    $exec = $this->imagickdir.'convert';
    $exec .= ' "'.realpath($this->source_filepath).'"';

    foreach ($this->commands as $command => $params)
    {
      $exec .= ' -'.$command;
      if (!empty($params))
      {
        $exec .= ' '.$params;
      }
    }

    $dest = pathinfo($destination_filepath);
    $exec .= ' "'.realpath($dest['dirname']).'/'.$dest['basename'].'" 2>&1';
    @exec($exec, $returnarray);

    if (function_exists('ilog')) ilog($exec);
    if (is_array($returnarray) && (count($returnarray)>0) )
    {
      if (function_exists('ilog')) ilog('ERROR', $returnarray);
      foreach($returnarray as $line)
        trigger_error($line, E_USER_WARNING);
    }
    return is_array($returnarray);
  }
}

// +-----------------------------------------------------------------------+
// |                       Class for GD library                            |
// +-----------------------------------------------------------------------+

class GdImage implements ImageInterface
{
  var $image;
  var $quality = 95;

  function __construct($source_filepath)
  {
    $gd_info = gd_info();
    $extension = strtolower(get_extension($source_filepath));

    if (in_array($extension, array('jpg', 'jpeg')))
    {
      $this->image = imagecreatefromjpeg($source_filepath);
    }
    else if ($extension == 'png')
    {
      $this->image = imagecreatefrompng($source_filepath);
    }
    elseif ($extension == 'gif' and $gd_info['GIF Read Support'] and $gd_info['GIF Create Support'])
    {
      $this->image = imagecreatefromgif($source_filepath);
    }
    else
    {
      die('[Image GD] unsupported file extension');
    }
  }

  function get_width()
  {
    return imagesx($this->image);
  }

  function get_height()
  {
    return imagesy($this->image);
  }

  function crop($width, $height, $x, $y)
  {
    $dest = imagecreatetruecolor($width, $height);

    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    if (function_exists('imageantialias'))
    {
      imageantialias($dest, true);
    }

    $result = imagecopymerge($dest, $this->image, 0, 0, $x, $y, $width, $height, 100);

    if ($result !== false)
    {
      imagedestroy($this->image);
      $this->image = $dest;
    }
    else
    {
      imagedestroy($dest);
    }
    return $result;
  }

  function strip()
  {
    return true;
  }

  function rotate($rotation)
  {
    $dest = imagerotate($this->image, $rotation, 0);
    imagedestroy($this->image);
    $this->image = $dest;
    return true;
  }

  function set_compression_quality($quality)
  {
    $this->quality = $quality;
    return true;
  }

  function resize($width, $height)
  {
    $dest = imagecreatetruecolor($width, $height);

    imagealphablending($dest, false);
    imagesavealpha($dest, true);
    if (function_exists('imageantialias'))
    {
      imageantialias($dest, true);
    }

    $result = imagecopyresampled($dest, $this->image, 0, 0, 0, 0, $width, $height, $this->get_width(), $this->get_height());

    if ($result !== false)
    {
      imagedestroy($this->image);
      $this->image = $dest;
    }
    else
    {
      imagedestroy($dest);
    }
    return $result;
  }

  function sharpen($amount)
  {
    $m = Image::get_sharpen_matrix($amount);
    return imageconvolution($this->image, $m, 1, 0);
  }

  function compose($overlay, $x, $y, $opacity)
  {
    $ioverlay = $overlay->image->image;
    /* A replacement for php's imagecopymerge() function that supports the alpha channel
    See php bug #23815:  http://bugs.php.net/bug.php?id=23815 */

    $ow = imagesx($ioverlay);
    $oh = imagesy($ioverlay);

		// Create a new blank image the site of our source image
		$cut = imagecreatetruecolor($ow, $oh);

		// Copy the blank image into the destination image where the source goes
		imagecopy($cut, $this->image, 0, 0, $x, $y, $ow, $oh);

		// Place the source image in the destination image
		imagecopy($cut, $ioverlay, 0, 0, 0, 0, $ow, $oh);
		imagecopymerge($this->image, $cut, $x, $y, 0, 0, $ow, $oh, $opacity);
    imagedestroy($cut);
    return true;
  }

  function write($destination_filepath)
  {
    $extension = strtolower(get_extension($destination_filepath));

    if ($extension == 'png')
    {
      imagepng($this->image, $destination_filepath);
    }
    elseif ($extension == 'gif')
    {
      imagegif($this->image, $destination_filepath);
    }
    else
    {
      imagejpeg($this->image, $destination_filepath, $this->quality);
    }
  }

  function destroy()
  {
    imagedestroy($this->image);
  }
}
