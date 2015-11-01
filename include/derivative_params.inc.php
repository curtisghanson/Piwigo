<?php
// +-----------------------------------------------------------------------+
// | Piwigo - a PHP based photo gallery                                    |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2008-2014 Piwigo Team                  http://piwigo.org |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation                                          |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, |
// | USA.                                                                  |
// +-----------------------------------------------------------------------+

/**
 * @package Derivatives
 */


/**
 * Formats a size name into a 2 chars identifier usable in filename.
 *
 * @param string $t one of IMG_*
 * @return string
 */
function derivative_to_url($t)
{
  return substr($t, 0, 2);
}

/**
 * Formats a size array into a identifier usable in filename.
 *
 * @param int[] $s
 * @return string
 */
function size_to_url($s)
{
  if ($s[0]==$s[1])
  {
    return $s[0];
  }
  return $s[0].'x'.$s[1];
}

/**
 * @param int[] $s1
 * @param int[] $s2
 * @return bool
 */
function size_equals($s1, $s2)
{
  return ($s1[0]==$s2[0] && $s1[1]==$s2[1]);
}

/**
 * Converts a char a-z into a float.
 *
 * @param string
 * @return float
 */
function char_to_fraction($c)
{
	return (ord($c) - ord('a'))/25;
}

/**
 * Converts a float into a char a-z.
 *
 * @param float
 * @return string
 */
function fraction_to_char($f)
{
	return chr(ord('a') + round($f*25));
}
