<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlNavBlockSiteComponent.class.php,v 1.17 2007/10/25 17:44:12 adamfranco Exp $
 */ 

/**
 * The NavBlock component is a hierarchal node that provides a gateway to a 
 * sub-level of the hierarchy.
 * 
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlNavBlockSiteComponent.class.php,v 1.17 2007/10/25 17:44:12 adamfranco Exp $
 */
class XmlNavBlockSiteComponent
	extends XmlBlockSiteComponent
	// implements NavBlockSiteComponent
{

	/**
	 * Populate this object with default values
	 * 
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function populateWithDefaults () {
		parent::populateWithDefaults();
		
		$this->setOrganizer($this->_director->createSiteComponent(new Type('segue', 'edu.middlebury', "NavOrganizer")));
	}
		
	/**
	 * Answers the organizer for this object
	 * 
	 * @return object OrganizerSiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	function getOrganizer () {
		$child = $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'NavOrganizer') {
				$navOrg = $child;
				$navOrgObj = $this->_director->getSiteComponent($navOrg);
			}
			$child = $child->nextSibling;
		}
		if (!isset($navOrgObj)) {
			$navOrgObj = $this->_director->createSiteComponent(new Type('segue', 'edu.middlebury', "NavOrganizer"));
			$navOrgObj->updateNumRows('1');
			$navOrgObj->updateNumColumns('1');
		}
		return $navOrgObj;
		
		//throwError( new Error("Organizer not found", "XmlSiteComponents"));
	}
	
	/**
	 * Answers the nested menu for this object
	 * 
	 * @return object OrganizerSiteComponent
	 * @access public
	 * @since 9/20/06
	 */
	function getNestedMenuOrganizer () {
		$menuOrgObj = null;
		$child = $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'MenuOrganizer') {
				$menuOrg = $child;
				$menuOrgObj = $this->_director->getSiteComponent($menuOrg);
			}
			$child = $child->nextSibling;
		}
// 		if (!isset($menuOrgObj)) {
// 			$menuOrgObj = $this->_director->createSiteComponent(new Type('segue', 'edu.middlebury', "MenuOrganizer"));
// 		}
		return $menuOrgObj;
		
		//throwError( new Error("Organizer not found", "XmlSiteComponents"));
	}
	
	/**
	 * Set the organizer for this NavBlock
	 * 
	 * @param object Organizer $organizer
	 * @return voiid
	 * @access public
	 * @since 3/31/06
	 */
	function setOrganizer ( $organizer ) {
		$orgElement = $organizer->getElement();
		$child = $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'NavOrganizer') {
				$this->_element->replaceChild($orgElement, $child);				
				return;	
			}
			$child = $child->nextSibling;
		}
		// organizer not found... create it
		$this->_element->appendChild($orgElement);
	}
	
	/**
	 * Answers the target Id for all NavBlocks in the menu
	 * 
	 * @return string the target id
	 * @access public
	 * @since 4/12/06
	 */
	function getTargetId () {
		$menuOrg = $this->getParentComponent();
		return $menuOrg->getTargetId();
	}

	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function acceptVisitor ( SiteVisitor $visitor, $inMenu = FALSE ) {
		return $visitor->visitNavBlock($this);
	}
	
	/**
	 * Make a child Menu nested
	 * 
	 * @param object MenuOrganizerSiteComponent $menuOrganizer
	 * @return string The Id of the original cell
	 * @access public
	 * @since 9/22/06
	 */
	function makeNested ( $menuOrganizer ) {
		// A cell will have no old parent if it is newly created.
		if ($oldParent = $menuOrganizer->getParentComponent()) {
			if (method_exists($oldParent, 'getCellForSubcomponent'))
				$oldCellId = $oldParent->getId()."_cell:".$oldParent->getCellForSubcomponent($menuOrganizer);
			else 
				$oldCellId = null;
			
			$oldParent->detatchSubcomponent($menuOrganizer);
		} else {
			$oldCellId = null;
		}
		
		$this->_element->appendChild($menuOrganizer->getElement());
		
		return $oldCellId;
	}
	
	/**
	 * Remove a subcomponent, but don't delete it from the director completely.
	 * This is used to remove nested menus.
	 * 
	 * @param object SiteComponent $subcomponent
	 * @return void
	 * @access public
	 * @since 9/22/06
	 */
	function detatchSubcomponent ( $subcomponent ) {
		$this->_element->removeChild($subcomponent->getElement());
	}
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
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
		
		$possibleDestinations = $this->_director->getVisibleComponents();
		$parent = $this->getParentComponent();
		foreach (array_keys($possibleDestinations) as $id) {
			if ($id == $parent->getId())
				continue;
			
			switch (strtolower(get_class($possibleDestinations[$id]))) {
				case 'xmlmenuorganizersitecomponent':
					$results[$id] = $possibleDestinations[$id];
					break;
				default:
					break;
			}
		}
		
		return $results;
	}
	
	/**
	 * Answer the NavOrganizer above this nav block.
	 * 
	 * @return object NavOrganizerSiteComponent
	 * @access public
	 * @since 7/27/06
	 */
	function getParentNavOrganizer () {
		$parent = $this->getParentComponent();
		if ($parent)
			return $parent->getParentNavOrganizer();
		else {
			$null = null;
			return $null;
		}
	}
	
	/**
	 * Answer the menu for this component
	 * 
	 * @return object MenuOrganizerSiteComponent
	 * @access public
	 * @since 7/28/06
	 */
	function getMenuOrganizer () {
		return $this->getParentComponent();
	}
	
	/**
	 * Answer true if there is a level of menus below the current one.
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/22/06
	 */
	function subMenuExists () {
		if (!is_null($this->getNestedMenuOrganizer()))
			return TRUE;
		else {
			$organizer = $this->getOrganizer();
			return $organizer->subMenuExists();
		}
	}
}

?>