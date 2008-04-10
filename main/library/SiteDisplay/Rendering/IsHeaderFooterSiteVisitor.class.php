<?php
/**
 * @since 9/24/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: IsHeaderFooterSiteVisitor.class.php,v 1.1 2008/04/10 20:49:02 adamfranco Exp $
 */ 

require_once(dirname(__FILE__).'/HasMenuBelowSiteVisitor.class.php');

/**
 * This site visitor determines what parts of a site are 'header' or 'footer' parts.
 * Header/footer parts are defined as such:
 * 		- If a site has no menus, no header/footer exists.
 *		- If a site has menus:
 *			- Headers/footers are components that are not children of the top-level menu.
 * 
 * @since 9/24/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: IsHeaderFooterSiteVisitor.class.php,v 1.1 2008/04/10 20:49:02 adamfranco Exp $
 */
class IsHeaderFooterSiteVisitor
	implements SiteVisitor
{

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 4/10/08
	 */
	public function __construct () {
		$this->hasMenuBelowVisitor = new HasMenuBelowSiteVisitor;
	}
	
	/**
	 * Answer true if the component passed is the direct child of the site nav organizer.
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return boolean
	 * @access private
	 * @since 4/10/08
	 */
	private function isChildOfSiteNavOrg (SiteComponent $siteComponent) {
		if (!isset($this->siteNavOrgId)) {
			$siteNav = $siteComponent->getDirector()->getRootSiteComponent($siteComponent);
			$this->siteNavOrgId = $siteNav->getOrganizer()->getId();
		}
		if ($this->siteNavOrgId == $siteComponent->getParentComponent()->getId())
			return true;
		else
			return false;
	}
	
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
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
	 * @since 8/31/07
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
	 * @since 8/31/07
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
	 * @since 8/31/07
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteComponent ) {
		// If there is no menu in the site, then by definition there is no header
		// or footer
		return $siteComponent->acceptVisitor($this->hasMenuBelowVisitor);
	}
	
	/**
	 * Visit a Fixed Organizer
	 * 
	 * @param object FixedOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $siteComponent ) {
		if ($siteComponent->acceptVisitor($this->hasMenuBelowVisitor))
			return false;
		
		// if this is a child of the site nav organizer and the site
		// does have a menu, then this can be a header
		else if ($this->isChildOfSiteNavOrg($siteComponent) 
				&& $siteComponent->getParentComponent()->acceptVisitor(
					$this->hasMenuBelowVisitor))
			return true;
		
		else
			return $siteComponent->getParentComponent()->acceptVisitor($this);
	}
	
	/**
	 * Visit a the fixed Organizer of a nav block
	 * 
	 * @param object NavOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $siteComponent ) {
		return $this->visitFixedOrganizer($siteComponent);
	}
	
	/**
	 * Visit a Flow/Content Organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $siteComponent ) {
		if ($siteComponent->acceptVisitor($this->hasMenuBelowVisitor))
			return false;
		
		// if this is a child of the site nav organizer and the site
		// does have a menu, then this can be a header
		else if ($this->isChildOfSiteNavOrg($siteComponent) 
				&& $siteComponent->getParentComponent()->acceptVisitor(
					$this->hasMenuBelowVisitor))
			return true;
		
		else
			return $siteComponent->getParentComponent()->acceptVisitor($this);
	}
	
	/**
	 * Visit a MenuOrganizerSiteComponent
	 * 
	 * @param object MenuOrganizerSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $siteComponent ) {
		return false;
	}
	
}

?>