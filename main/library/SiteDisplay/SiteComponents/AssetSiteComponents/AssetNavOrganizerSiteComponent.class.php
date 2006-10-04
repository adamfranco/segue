<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetNavOrganizerSiteComponent.class.php,v 1.1 2006/10/04 20:36:19 adamfranco Exp $
 */ 

/**
 * The Organizer that is the direct child of a NavBlock.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetNavOrganizerSiteComponent.class.php,v 1.1 2006/10/04 20:36:19 adamfranco Exp $
 */
class AssetNavOrganizerSiteComponent
	extends AssetFixedOrganizerSiteComponent 
	// implements NavOrganizerSiteComponent
{
	
	/**
	 * Answer a displayName for this organizer. (Generally, a type or classification).
	 * 
	 * @return string
	 * @access public
	 * @since 4/10/06
	 */
	function getDisplayName () {
		$parent =& $this->getParentComponent();
		return $parent->getDisplayName()._(" <em>Organizer</em>");
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
		return $visitor->visitNavOrganizer($this);
	}
	
/*********************************************************
 * Drag & Drop destinations
 *********************************************************/
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
	 *
	 * NavOrganizers cannot be moved.
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
		$parent =& $this->getParentComponent();
		$parentNav =& $parent->getParentNavOrganizer();
		if ($parentNav)
			$results[$parentNav->getId()] =& $parentNav;
		else
			return $results;
		
		
		// As are FixedOrganizers that are below the parent NavOrganizer, but
		// not below me.
		$parentNavsFixedOrganizers =& $parentNav->getFixedOrganizers();
		
		$myFixedOrganizerIds = array_keys($this->getFixedOrganizers());
		
		foreach (array_keys($parentNavsFixedOrganizers) as $id) {
			if ($id != $this->getId() && !in_array($id, $myFixedOrganizerIds))
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
		return $this;
	}
	
	/**
	 * Answer the menu for this component
	 * 
	 * @return object MenuOrganizerSiteComponent
	 * @access public
	 * @since 7/28/06
	 */
	function &getMenuOrganizer () {		
		$parent =& $this->getParentComponent();
		return $parent->getMenuOrganizer();
	}
}

?>