<?php
/**
 * @since 6/6/08
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: IsBlockVisitor.class.php,v 1.2 2007/08/31 16:34:57 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/SiteVisitor.interface.php");

/**
 * Return true if passed to a block
 * 
 * @since 6/6/08
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: IsBlockVisitor.class.php,v 1.2 2007/08/31 16:34:57 achapin Exp $
 */
class IsRootMenuVisitor 
	implements SiteVisitor
{
	/**
	 * @var boolean $_started;  
	 * @access private
	 * @since 6/6/08
	 */
	private $_started = false;
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/6/08
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		return $siteComponent->getParentComponent()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/6/08
	 */
	public function visitBlockInMenu ( BlockSiteComponent $siteComponent ) {
		return $siteComponent->getParentComponent()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Navigation Block
	 * 
	 * @param object NavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/6/08
	 */
	public function visitNavBlock ( NavBlockSiteComponent $siteComponent ) {
		return $siteComponent->getParentComponent()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Site Navigation Block
	 * 
	 * @param object SiteNavBlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/6/08
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		// If we hit the top, the one passed was the root menu.
		return true;
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/6/08
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		return $siteComponent->getParentComponent()->acceptVisitor($this);
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/6/08
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		return $siteComponent->getParentComponent()->acceptVisitor($this);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/6/08
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		return $siteComponent->getParentComponent()->acceptVisitor($this);
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 6/6/08
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		if (!$this->_started) {
			$this->_started = true;
			return $siteComponent->getParentComponent()->acceptVisitor($this);
		}
		
		// If we hit a second menu, the one passed wasn't root.
		return false;
	}
}

?>