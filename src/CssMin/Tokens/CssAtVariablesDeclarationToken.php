<?php
namespace CssMin\Tokens;

use CssMin\Tokens\AbstractCssDeclarationToken;

/**
 * This {@link AbstractCssToken CSS token} represents a declaration of a @variables at-rule block.
 * 
 * @package		CssMin/Tokens
 * @link		http://code.google.com/p/cssmin/
 * @author		Joe Scylla <joe.scylla@gmail.com>
 * @copyright	2008 - 2011 Joe Scylla <joe.scylla@gmail.com>
 * @license		http://opensource.org/licenses/mit-license.php MIT License
 * @version		3.0.1
 */
class CssAtVariablesDeclarationToken extends AbstractCssDeclarationToken
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
