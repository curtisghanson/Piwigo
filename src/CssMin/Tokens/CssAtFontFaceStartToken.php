<?php
namespace CssMin\Tokens;

use CssMin\Tokens\AbstractCssAtBlockStartToken;

/**
 * This {@link AbstractCssToken CSS token} represents the start of a @font-face at-rule block.
 *
 * @package		CssMin/Tokens
 * @link		http://code.google.com/p/cssmin/
 * @author		Joe Scylla <joe.scylla@gmail.com>
 * @copyright	2008 - 2011 Joe Scylla <joe.scylla@gmail.com>
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 * @version		3.0.1
 */
class CssAtFontFaceStartToken extends AbstractCssAtBlockStartToken
	{
	/**
	 * Implements {@link AbstractCssToken::__toString()}.
	 * 
	 * @return string
	 */
	public function __toString()
		{
		return "@font-face{";
		}
	}
