<?php
/**
 * @since 7/14/08
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This inteface defines methods needed for 'text templates', Segue's way of 
 * supporting pluggable strings in HTML. 
 * 
 * @since 7/14/08
 * @package segue.wiki
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
interface Segue_Wiki_TextTemplate {
	
	/**
	 * Generate HTML given a set of parameters.
	 * 
	 * @param array $paramList
	 * @return string The HTML markup
	 * @access public
	 * @since 7/14/08
	 */
	public function generate (array $paramList);
	
	/**
	 * Answer an array of strings in the HTML that look like this template's output
	 * and list of parameters that the HTML corresponds to. e.g:
	 * 	array(
	 *		"<img src='http://www.example.net/test.jpg' width='350px'/>" 
	 *				=> array (	'server'	=> 'www.example.net',
	 *							'file'		=> 'test.jp',
	 * 							'width'		=> '350px'))
	 * 
	 * This method may throw an UnimplementedException if this is not supported.
	 *
	 * @param string $text
	 * @return array
	 * @access public
	 * @since 7/14/08
	 */
	public function getHtmlMatches ($text);
	
}

?>