<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: VisibilitySiteVisitor.class.php,v 1.4 2006/04/12 14:48:46 adamfranco Exp $
 */ 

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
 * @version $Id: VisibilitySiteVisitor.class.php,v 1.4 2006/04/12 14:48:46 adamfranco Exp $
 */
class VisibilitySiteVisitor {
		
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
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object Component 
	 * @access public
	 * @since 4/3/06
	 */
	function &visitBlock ( &$block ) {
		$this->_visibleComponents[$block->getId()] =& $block;
		$results = array();
		$results['VisibleComponents'] =& $this->_visibleComponents;
		$results['FilledTargetIds'] =& $this->_filledTargetIds;
		return $results;
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return object Component 
	 * @access public
	 * @since 4/3/06
	 */
	function &visitNavBlock ( &$navBlock ) {		
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
		if ($navBlock->isActive()) {
			$childOrganizer =& $navBlock->getOrganizer();
			$childOrganizer->acceptVisitor($this);
		}
		
		$this->_filledTargetIds[] = $navBlock->getTargetId();
		
		return $this->visitBlock($navBlock);
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
	function &visitSiteNavBlock ( &$siteNavBlock ) {
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
		if ($siteNavBlock->isActive()) {
			$childOrganizer =& $siteNavBlock->getOrganizer();
			$childOrganizer->acceptVisitor($this);
		}
				
		return $this->visitBlock($siteNavBlock);
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
	function &visitFixedOrganizer ( &$organizer ) {		
		// Ordered indicies are to be used in a left-right/top-bottom manner, but
		// may be returned in various orders to reflect another underlying fill direction.
		$orderedIndices = $organizer->getVisibleOrderedIndices();
		
		foreach ($orderedIndices as $i) {
			$child =& $organizer->getSubcomponentForCell($i);
			if (is_object($child))
				$child->acceptVisitor($this);
		}
		
		return $this->visitBlock($organizer);
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
	function &visitNavOrganizer ( &$organizer ) {
		return $this->visitFixedOrganizer($organizer);
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitFlowOrganizer( &$organizer ) {
		return $this->visitFixedOrganizer($organizer);
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
	function &visitMenuOrganizer ( &$organizer ) {	
		return $this->visitFlowOrganizer($organizer);
	}
}

?>