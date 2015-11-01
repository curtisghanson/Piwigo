<?php
namespace CssMin\Tokens;

use CssMin\Tokens\AbstractCssToken;

/**
 * This {@link AbstractCssToken CSS token} is a utility token that extends {@link aNullToken} and returns only a empty string.
 *
 * @package		CssMin/Tokens
 * @link		http://code.google.com/p/cssmin/
 * @author		Joe Scylla <joe.scylla@gmail.com>
 * @copyright	2008 - 2011 Joe Scylla <joe.scylla@gmail.com>
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 * @version		3.0.1
 */
class CssNullToken extends AbstractCssToken
	{
	/**
	 * Implements {@link AbstractCssToken::__toString()}.
	 * 
	 * @return string
	 */
	public function __toString()
		{
		return "";
		}
	}
