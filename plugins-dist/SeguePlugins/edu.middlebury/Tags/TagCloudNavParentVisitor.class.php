<?php
/**
 * @since 4/10/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagCloudNavParentVisitor.class.php,v 1.2 2008/04/11 20:38:59 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/Rendering/SiteVisitor.interface.php");

/**
 * This Visitor will return the first navigational node above the site componenet passed
 * to it.
 * 
 * @since 4/10/08
 * @package segue.plugins.Segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagCloudNavParentVisitor.class.php,v 1.2 2008/04/11 20:38:59 adamfranco Exp $
 */
class TagCloudNavParentVisitor
	implements SiteVisitor
{
		
	/**
	 * Visit a Block
	 * 
	 * @param object BlockSiteComponent $siteComponent
	 * @return mixed
	 * @access public
	 * @since 8/31/07
	 */
	public function visitBlock ( BlockSiteComponent $siteComponent ) {
		$parent = $siteComponent->getParentComponent();
		if (!$parent)
			throw new OperationFailedException ("No parent for ".$siteComponent->getId());
		return $parent->acceptVisitor($this);
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
		$parent = $siteComponent->getParentComponent();
		if (!$parent)
			throw new OperationFailedException ("No parent for ".$siteComponent->getId());
		return $parent->acceptVisitor($this);
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
		return $siteComponent;
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
		return $siteComponent;
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
		$parent = $siteComponent->getParentComponent();
		if (!$parent)
			throw new OperationFailedException ("No parent for ".$siteComponent->getId());
		return $parent->acceptVisitor($this);
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
		$parent = $siteComponent->getParentComponent();
		if (!$parent)
			throw new OperationFailedException ("No parent for ".$siteComponent->getId());
		return $parent->acceptVisitor($this);
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
		$parent = $siteComponent->getParentComponent();
		if (!$parent)
			throw new OperationFailedException ("No parent for ".$siteComponent->getId());
		return $parent->acceptVisitor($this);
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
		$parent = $siteComponent->getParentComponent();
		if (!$parent)
			throw new OperationFailedException ("No parent for ".$siteComponent->getId());
		return $parent->acceptVisitor($this);
	}
	
}
