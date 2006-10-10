<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetMenuOrganizerSiteComponent.class.php,v 1.3 2006/10/10 19:38:30 adamfranco Exp $
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
 * @version $Id: AssetMenuOrganizerSiteComponent.class.php,v 1.3 2006/10/10 19:38:30 adamfranco Exp $
 */
class AssetMenuOrganizerSiteComponent 
	extends AssetFlowOrganizerSiteComponent
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
	 * Answers the target Id for all NavBlocks in the menu
	 * 
	 * @return string the target id
	 * @access public
	 * @since 4/12/06
	 */
	function getTargetId () {
		if ($this->_element->hasAttribute('target_id')) {
			$assetId =& $this->_asset->getId();
			return $assetId->getIdString()."----".$this->_element->getAttribute('target_id');
		}

		throwError( new Error("No target_id available ".$this->_element->toString(true), "XmlSiteComponents"));		
	}

	/**
	 * Update the target Id
	 * 
	 * @param string Id
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateTargetId ($id) {
		preg_match('/^(.+----)?(.+_cell:[0-9]+)$/i', $id, $matches);
		$this->_element->setAttribute('target_id', $matches[2]);
		$this->_saveXml();
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
	
	/**
	 * Answer true if there is a level of menus below the current one.
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/22/06
	 */
	function subMenuExists () {
		// This is a submenu, so return true.
		return true;
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
		
		// Add our parent NavBlock
		$parentNavBlock =& $parentNav->getParentComponent();
		$results[$parentNavBlock->getId()] =& $parentNavBlock;
		
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
		
		// if this menu is nested, return the parent's nav organizer
		if (preg_match('/^.*NavBlockSiteComponent$/i', get_class($parent)))
			return $parent->getOrganizer();
		
		// otherwise traverse up
		else
			return $parent->getParentNavOrganizer();
	}
}

?>