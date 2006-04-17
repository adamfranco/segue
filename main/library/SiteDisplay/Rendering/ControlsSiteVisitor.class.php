<?php
/**
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.1 2006/04/17 16:14:39 adamfranco Exp $
 */ 

/**
 * Returns the controls strings for each component type
 * 
 * @since 4/17/06
 * @package segue.library.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ControlsSiteVisitor.class.php,v 1.1 2006/04/17 16:14:39 adamfranco Exp $
 */
class ControlsSiteVisitor {
		
	/**
	 * Answer common controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	function &visitCommon ( &$siteComponent ) {
		ob_start();
		print "\n\t\t\t\tControls:";
		
		$controls = ob_get_clean();
		return $controls;
	}
	
	/**
	 * Answer controls for Block SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	function &visitBlock ( &$siteComponent ) {
		return $this->visitCommon($siteComponent);
	}
	
	/**
	 * Answer controls for NavBlock SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	function &visitNavBlock ( &$siteComponent ) {
		return $this->visitCommon($siteComponent);
	}
	
	/**
	 * Answer controls for FixedOrganizer SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	function &visitFixedOrganizer ( &$siteComponent ) {
		return $this->visitCommon($siteComponent);
	}
	
	/**
	 * Answer controls for NavOrganizer SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	function &visitNavOrganizer ( &$siteComponent ) {
		return $this->visitCommon($siteComponent);
	}
	
	/**
	 * Answer controls for FlowOrganizer SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	function &visitFlowOrganizer ( &$siteComponent ) {
		return $this->visitCommon($siteComponent);
	}
	
	/**
	 * Answer controls for MenuOrganizer SiteComponents
	 * 
	 * @param SiteComponent $siteComponent
	 * @return string
	 * @access public
	 * @since 4/17/06
	 */
	function &visitMenuOrganizer ( &$siteComponent ) {
		return $this->visitCommon($siteComponent);
	}
	
}

?>