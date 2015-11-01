<?php
namespace Piwigo\Derivative;

/**
 * Container for watermark configuration.
 */
final class WatermarkParams
{
  /** @var string */
  public $file = '';
  /** @var int[] */
  public $min_size = array(500,500);
  /** @var int */
  public $xpos = 50;
  /** @var int */
  public $ypos = 50;
  /** @var int */
  public $xrepeat = 0;
  /** @var int */
  public $opacity = 100;
}
