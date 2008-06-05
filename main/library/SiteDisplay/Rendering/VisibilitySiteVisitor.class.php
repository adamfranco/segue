<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: VisibilitySiteVisitor.class.php,v 1.9 2007/09/04 15:05:32 adamfranco Exp $
 */ 
 
require_once(MYDIR."/main/modules/view/ViewModeSiteVisitor.class.php");

/**
 * The VisibilityVisitor traverses the site hierarchy, recording the visibility of
 * each component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: VisibilitySiteVisitor.class.php,v 1.9 2007/09/04 15:05:32 adamfranco Exp $
 */
class VisibilitySiteVisitor 
	implements SiteVisitor
{
		
	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	function VisibilitySiteVisitor () {
		$this->_visibleComponents = array();
		$this->_filledTargetIds = array();
	}
	
	/**
	 * Visit any kind of SiteComponent and record its visibility
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return array
	 * @access public
	 * @since 8/31/07
	 */
	private function visitSiteComponent ( SiteComponent $siteComponent) {
		$this->_visibleComponents[$siteComponent->getId()] = $siteComponent;
		$results = array();
		$results['VisibleComponents'] = $this->_visibleComponents;
		$results['FilledTargetIds'] = $this->_filledTargetIds;
		return $results;
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return array
	 * @access public
	 * @since 4/3/06
	 */
	public function visitBlock ( BlockSiteComponent $block ) {
		return $this->visitSiteComponent($block);
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
		$this->visitBlock($siteComponent);
	}

	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return object Component 
	 * @access public
	 * @since 4/3/06
	 */
	public function visitNavBlock ( NavBlockSiteComponent $navBlock ) {		
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
		if ($navBlock->isActive()) {
			$childOrganizer = $navBlock->getOrganizer();
			$childOrganizer->acceptVisitor($this);
			
			$nestedMenuOrganizer = $navBlock->getNestedMenuOrganizer();
			if (!is_null($nestedMenuOrganizer))
				$nestedMenuOrganizer->acceptVisitor($this);
		}
		
		return $this->visitSiteComponent($navBlock);
	}
	
	/**
	 * Visit a SiteNavBlock and return the site GUI component that corresponds to
	 *	it.
	 * 
	 * @param object SiteNavBlockSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteNavBlock ) {
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
		if ($siteNavBlock->isActive()) {
			$childOrganizer = $siteNavBlock->getOrganizer();
			$childOrganizer->acceptVisitor($this);
		}
				
		return $this->visitSiteComponent($siteNavBlock);
	}
	
	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object OrganizerSiteComponent $organizer
	 * @return object Component
	 * @access private
	 * @since 4/3/06
	 */
	private function visitOrganizer ( OrganizerSiteComponent $organizer ) {		
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $organizer->getSubcomponentForCell($i);
			if (is_object($child))
				$child->acceptVisitor($this);
		}
		
		return $this->visitSiteComponent($organizer);
	}

	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $organizer ) {		
		return $this->visitOrganizer($organizer);
	}
	
	
	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	public function visitNavOrganizer ( NavOrganizerSiteComponent $organizer ) {
		return $this->visitOrganizer($organizer);
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		return $this->visitOrganizer($organizer);
	}
	
	/**
	 * Visit a menu organizer and return the menu GUI component that corresponds
	 * to it.
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {	
		$this->_filledTargetIds[$organizer->getId()] = $organizer->getTargetId();
		return $this->visitFlowOrganizer($organizer);
	}
}

?>