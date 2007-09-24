<?php
/**
 * @since 9/24/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editHeader.act.php,v 1.1 2007/09/24 20:49:09 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/view.act.php");
require_once(dirname(__FILE__)."/Rendering/EditHeaderFooterSiteVisitor.class.php");


/**
 * <##>
 * 
 * @since 9/24/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editHeader.act.php,v 1.1 2007/09/24 20:49:09 adamfranco Exp $
 */
class editHeaderAction
	extends viewAction
{
		
	/**
	 * Answer the appropriate site visitor for this action
	 * 
	 * @return object SiteVisitor
	 * @access public
	 * @since 9/24/07
	 */
	function getSiteVisitor () {
		if (!isset($this->visitor)) {
			
			$requestedNode = $this->_director->getSiteComponentById(
				RequestContext::value("node"));
			
			$this->visitor = new EditHeaderFooterSiteVisitor();
		}
		return $this->visitor;
	}
	
	/**
	 * Answer a links back to the main Segue pages
	 * 
	 * @return object GUIComponent
	 * @access public
	 * @since 9/24/07
	 */
	function getCommandsComponent () {
		return new Component('', BLANK, 2);
	}
	
	/**
	 * Answer a links back to the main Segue pages
	 * 
	 * @return object GUIComponent
	 * @access public
	 * @since 9/24/07
	 */
	function getSegueLinksComponent () {
		return new Component('', BLANK, 2);
	}
	
	/**
	 * Answer a links back to the main Segue pages
	 * 
	 * @return object GUIComponent
	 * @access public
	 * @since 9/24/07
	 */
	function getLoginComponent () {
		return new Component('', BLANK, 2);
	}
	
}

?>