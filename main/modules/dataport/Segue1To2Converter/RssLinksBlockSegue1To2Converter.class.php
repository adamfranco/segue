<?php
/**
 * @since 3/19/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RssLinksBlockSegue1To2Converter.class.php,v 1.1 2008/03/21 20:28:37 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/BreadcrumbsBlockSegue1To2Converter.class.php");

/**
 * A converter for text blocks
 * 
 * @since 3/19/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RssLinksBlockSegue1To2Converter.class.php,v 1.1 2008/03/21 20:28:37 adamfranco Exp $
 */
class RssLinksBlockSegue1To2Converter
	extends BreadcrumbsBlockSegue1To2Converter
{
	/**
	 * Answer a new Type DOMElement for this plugin
	 * 
	 * @return DOMElement
	 * @access protected
	 * @since 2/12/08
	 */
	protected function createMyPluginType () {
		return $this->createPluginType('Rsslinks');
	}
}

?>