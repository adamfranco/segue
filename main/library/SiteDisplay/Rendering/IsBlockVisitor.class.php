<?php
/**
 * @since 5/24/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: IsBlockVisitor.class.php,v 1.1 2007/05/24 19:56:26 adamfranco Exp $
 */ 

/**
 * Return true if passed to a block
 * 
 * @since 5/24/07
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: IsBlockVisitor.class.php,v 1.1 2007/05/24 19:56:26 adamfranco Exp $
 */
class IsBlockVisitor {
	
	/**
	 * Visit a block 
	 * 
	 * @param object BlockSiteComponent $block
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function &visitBlock ( &$block ) {
		$true = true;
		return $true;
	}
	
	/**
	 * Visit a nav block
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function &visitNavBlock ( &$navBlock ) {		
		$false = false;
		return $false;
	}
	
	/**
	 * Visit a SiteNavBlock
	 * 
	 * @param object SiteNavBlockSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function &visitSiteNavBlock ( &$siteNavBlock ) {
		$false = false;
		return $false;
	}

	/**
	 * Visit a fixed organizer
	 *
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function &visitFixedOrganizer ( &$organizer ) {		
		$false = false;
		return $false;
	}
	
	
	/**
	 * Visit a fixed organizer 
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function &visitNavOrganizer ( &$organizer ) {
		$false = false;
		return $false;
	}
	
	/**
	 * Visit a flow organizer
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function &visitFlowOrganizer( &$organizer ) {
		$false = false;
		return $false;
	}
	
	/**
	 * Visit a menu organizer
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function &visitMenuOrganizer ( &$organizer ) {	
		$false = false;
		return $false;
	}
}

?>