<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlMenuOrganizerSiteComponent.class.php,v 1.3 2006/04/11 21:06:25 adamfranco Exp $
 */ 

/**
 * The Menu organizer site component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlMenuOrganizerSiteComponent.class.php,v 1.3 2006/04/11 21:06:25 adamfranco Exp $
 */
class XmlMenuOrganizerSiteComponent 
	extends XmlFlowOrganizerSiteComponent
	// implements MenuOrganizerSiteComponent
{
	/**
	 * Answer a displayName for this organizer. (Generally, a type or classification).
	 * 
	 * @return string
	 * @access public
	 * @since 4/10/06
	 */
	function getDisplayName () {
		return _("<em>Menu Organizer</em>");
	}
	
	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &acceptVisitor ( &$visitor ) {
		return $visitor->visitMenuOrganizer($this);
	}
	
/*********************************************************
 * Drag & Drop destinations
 *********************************************************/
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
	 *
	 * For fixed organizers the possible destinations are cells in the parent
	 * NavOrganizer or FixedOrganizers under that NavOrganizer
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/11/06
	 */
	function &getVisibleDestinationsForPossibleAddition () {
		$results = array();
		
		// If not authorized to remove this item, return an empty array;
		// @todo
		if(false) {
			return $results;
		}
		
		// The parent NavOrganizer is a possible destination
		$parentNav =& $this->getParentNavOrganizer();
		$results[$parentNav->getId()] =& $parentNav;
		
		// As are FixedOrganizers that are below the parent NavOrganizer, but
		// not below me.
		$parentNavsFixedOrganizers =& $parentNav->getFixedOrganizers();
				
		foreach (array_keys($parentNavsFixedOrganizers) as $id) {
			$results[$id] =& $parentNavsFixedOrganizers[$id];
		}
		
		return $results;
	}
	
	/**
	 * Answer the NavOrganizer above this organizer.
	 * 
	 * @return object NavOrganizerSiteComponent
	 * @access public
	 * @since 4/11/06
	 */
	function &getParentNavOrganizer () {
		$parent =& $this->getParentComponent();
		return $parent->getParentNavOrganizer();
	}
}

?>