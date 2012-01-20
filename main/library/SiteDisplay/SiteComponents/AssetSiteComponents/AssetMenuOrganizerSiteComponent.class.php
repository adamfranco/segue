<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetMenuOrganizerSiteComponent.class.php,v 1.10 2008/04/17 19:39:21 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/../AbstractSiteComponents/MenuOrganizerSiteComponent.abstract.php");
require_once(MYDIR.'/main/library/SiteDisplay/Rendering/IsRootMenuVisitor.class.php');


/**
 * The Menu organizer site component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetMenuOrganizerSiteComponent.class.php,v 1.10 2008/04/17 19:39:21 achapin Exp $
 */
class AssetMenuOrganizerSiteComponent 
	extends AssetFlowOrganizerSiteComponent
	implements MenuOrganizerSiteComponent
{
	/**
	 * Answer a displayName for this organizer. (Generally, a type or classification).
	 * 
	 * @return string
	 * @access public
	 * @since 4/10/06
	 */
	function getDisplayName () {
		return _("<em>Menu</em>");
	}
	
	/**
	 * Answer the component class
	 * 
	 * @return string
	 * @access public
	 * @since 11/09/07
	 */
	function getComponentClass () {
		return "MenuOrganizer";
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
			$assetId = $this->_asset->getId();
			return $assetId->getIdString()."----".$this->_element->getAttribute('target_id');
		}
		
		// Look through the Fixed (layout) organizers for an empty cell which
		// we can use as the target since one isn't set.
		$destinations = $this->getVisibleDestinationsForPossibleAddition();
		// Look for empty cells in which to use as our target.
		foreach (array_reverse($destinations) as $organizer) {
			$numCells = $organizer->getTotalNumberOfCells();
			for ($i = 0; $i < $numCells; $i++) {
				if (is_null($organizer->getSubcomponentForCell($i))) {
					return $organizer->getId()."_cell:".$i;
				}
			}
		}
		
		throw new Exception("No target_id available in ".$this->_element->ownerDocument->saveXML($this->_element)."
		
		----------------------------------------------------- 
		Maybe one exists in: ".$this->_element->ownerDocument->saveXML());		
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
		if(!preg_match('/^(.+----)?(.+_cell:[0-9]+)$/i', $id, $matches))
			throw new Exception("Invalid Target Id, '$id'.");
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
	public function acceptVisitor ( SiteVisitor $visitor, $inMenu = FALSE ) {
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
	function getVisibleDestinationsForPossibleAddition () {
		$results = array();
		
		// If not authorized to remove this item, return an empty array;
		// @todo
		if(false) {
			return $results;
		}
		
		// The parent NavOrganizer is a possible destination
		$parentNav = $this->getParentNavOrganizer();
		$results[$parentNav->getId()] = $parentNav;
		
		// Add our parent NavBlock
		$parentNavBlock = $parentNav->getParentComponent();
		if (!$parentNavBlock->getNestedMenuOrganizer())
			$results[$parentNavBlock->getId()] = $parentNavBlock;
		
		// As are FixedOrganizers that are below the parent NavOrganizer, but
		// not below me.
		$parentNavsFixedOrganizers = $parentNav->getFixedOrganizers();
				
		foreach (array_keys($parentNavsFixedOrganizers) as $id) {
			$results[$id] = $parentNavsFixedOrganizers[$id];
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
	public function getParentNavOrganizer () {
		$parent = $this->getParentComponent();
		
		// if this menu is nested, return the parent's nav organizer
		if (preg_match('/^.*NavBlockSiteComponent$/i', get_class($parent)))
			return $parent->getOrganizer();
		
		// otherwise traverse up
		else
			return $parent->getParentNavOrganizer();
	}
	
	/**
	 * Answer the kind of menu Gui Component to display: Menu_Left, Menu_Right, Menu_Top, or Menu_Bottom
	 * 
	 * @return string
	 * @access public
	 * @since 5/12/08
	 */
	public function getDisplayType () {
		$element = $this->getElement();
		
		if (!$element->hasAttribute('displayType'))
			return 'Menu_Left';
		
		return $element->getAttribute('displayType');
	}
	
	/**
	 * Set the Gui Component display type for this menu, one of: 
	 * 		Menu_Left, Menu_Right, Menu_Top, or Menu_Bottom
	 * 
	 * @param string $displayType
	 * @return null
	 * @access public
	 * @since 5/12/08
	 */
	public function setDisplayType ($displayType) {
		if (!in_array($displayType, array('Menu_Left', 'Menu_Right', 'Menu_Top', 'Menu_Bottom')))
			throw new InvalidArgumentException("'$displayType' is not one of Menu_Left, Menu_Right, Menu_Top, or Menu_Bottom.");
			
		$element = $this->getElement();
		
		$element->setAttribute('displayType', $displayType);
		
		$this->_saveXml();
	}
	
	/**
	 * Answer true if this is the top-level menu.
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/6/08
	 */
	public function isRootMenu () {
		return $this->acceptVisitor(new IsRootMenuVisitor);
	}
}

?>