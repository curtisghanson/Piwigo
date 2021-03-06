<?php
namespace CssMin\Tokens;

use CssMin\Tokens\AbstractCssToken;

/**
 * Abstract definition of a for ruleset end token.
 *
 * @package		CssMin/Tokens
 * @link		http://code.google.com/p/cssmin/
 * @author		Joe Scylla <joe.scylla@gmail.com>
 * @copyright	2008 - 2011 Joe Scylla <joe.scylla@gmail.com>
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 * @version		3.0.1
 */
abstract class AbstractCssRulesetEndToken extends AbstractCssToken
	{
	/**
	 * Implements {@link AbstractCssToken::__toString()}.
	 * 
	 * @return string
	 */
	public function __toString()
		{
		return "}";
		}
	}
