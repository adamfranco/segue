<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteNodeRenderer.class.php,v 1.3 2006/01/26 21:15:18 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/NavigationNodeRenderer.class.php");

/**
 * The NodeRenderer class takes an Asset and renders its navegational item,
 * as well as its children if selected
 * 
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SiteNodeRenderer.class.php,v 1.3 2006/01/26 21:15:18 adamfranco Exp $
 */
class SiteNodeRenderer
	extends NavigationNodeRenderer
{
	/**
	 * Add parents to the active nodes array
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/19/06
	 */
	function traverseActiveUp () {
		$this->setActive();
		return;
	}
	
	/**
	 * Answer the top-level NodeRenderer
	 * 
	 * @return object NodeRenderer
	 * @access public
	 * @since 1/26/06
	 */
	function &getSiteRenderer () {
		return $this; 
	}
	
	/**
	 * Answer the GUI component for the contents of the site that this node
	 * is in.
	 * 
	 * @return object Component
	 * @access public
	 * @since 1/26/06
	 */
	function &renderSite () {
		return $this->renderTargetComponent();
	}
	
	/**
	 * Answer the site title is in.
	 * 
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function getSiteTitle () {
		return $this->_asset->getDisplayName();
	}
}