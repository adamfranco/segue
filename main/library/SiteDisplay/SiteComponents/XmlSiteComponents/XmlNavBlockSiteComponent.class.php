<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlNavBlockSiteComponent.class.php,v 1.11 2006/09/22 14:41:49 adamfranco Exp $
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
 * @version $Id: XmlNavBlockSiteComponent.class.php,v 1.11 2006/09/22 14:41:49 adamfranco Exp $
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
		
		$this->setOrganizer($this->_director->createSiteComponent("NavOrganizer"));
	}
		
	/**
	 * Answers the organizer for this object
	 * 
	 * @return object OrganizerSiteComponent
	 * @access public
	 * @since 4/3/06
	 */
	function &getOrganizer () {
		$child =& $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'NavOrganizer') {
				$navOrg =& $child;
				$navOrgObj =& $this->_director->getSiteComponent($navOrg);
			}
			$child =& $child->nextSibling;
		}
		if (!isset($navOrgObj)) {
			$navOrgObj =& $this->_director->createSiteComponent("NavOrganizer");
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
	function &getNestedMenuOrganizer () {
		$menuOrgObj = null;
		$child =& $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'MenuOrganizer') {
				$menuOrg =& $child;
				$menuOrgObj =& $this->_director->getSiteComponent($menuOrg);
			}
			$child =& $child->nextSibling;
		}
// 		if (!isset($menuOrgObj)) {
// 			$menuOrgObj =& $this->_director->createSiteComponent("MenuOrganizer");
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
	function setOrganizer ( &$organizer ) {
		$orgElement =& $organizer->getElement();
		$child =& $this->_element->firstChild;
		while ($child) {
			if ($child->nodeName == 'NavOrganizer') {
				$this->_element->replaceChild($orgElement, $child);				
				return;	
			}
			$child =& $child->nextSibling;
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
		$menuOrg =& $this->getParentComponent();
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
	function &acceptVisitor ( &$visitor ) {
		return $visitor->visitNavBlock($this);
	}
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
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
		
		$possibleDestinations =& $this->_director->getVisibleComponents();
		$parent =& $this->getParentComponent();
		foreach (array_keys($possibleDestinations) as $id) {
			if ($id == $parent->getId())
				continue;
			
			switch (strtolower(get_class($possibleDestinations[$id]))) {
				case 'xmlmenuorganizersitecomponent':
					$results[$id] =& $possibleDestinations[$id];
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
	function &getParentNavOrganizer () {
		$parent =& $this->getParentComponent();
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
	function &getMenuOrganizer () {
		return $this->getParentComponent();
	}
}

?>