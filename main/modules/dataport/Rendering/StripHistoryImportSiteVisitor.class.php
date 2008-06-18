<?php
/**
 * @since 6/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__).'/UntrustedAgentAndTimeDomImportSiteVisitor.class.php');

/**
 * This Importer strips out all history entries, as well as attributes all creation
 * to the current visitor at the current time.
 * 
 * @since 6/12/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class StripHistoryImportSiteVisitor
	extends UntrustedAgentAndTimeDomImportSiteVisitor
{
		
	/**
	 * Apply the historical versions to the plugin.
	 * 
	 * @param object SeguePluginsAPI $plugin
	 * @param object DOMElement $element
	 * @return void
	 * @access protected
	 * @since 1/23/08
	 */
	protected function applyPluginHistory (SeguePluginsAPI $plugin, DOMElement $element) {
		// do nothing
	}
	
}

?>