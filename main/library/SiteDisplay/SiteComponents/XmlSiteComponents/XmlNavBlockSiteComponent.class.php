<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlNavBlockSiteComponent.class.php,v 1.5 2006/04/11 21:06:25 adamfranco Exp $
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
 * @version $Id: XmlNavBlockSiteComponent.class.php,v 1.5 2006/04/11 21:06:25 adamfranco Exp $
 */
class XmlNavBlockSiteComponent
	extends XmlBlockSiteComponent
	// implements NavBlockSiteComponent
{
		
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
	 * Answer the target Id
	 * 
	 * @return string Id
	 * @access public
	 * @since 3/31/06
	 */
	function getTargetId () {
		if ($this->_element->hasAttribute('target_id'))
			return $this->_element->getAttribute('target_id');
		else
			throwError( new Error("No target_id available", "XmlSiteComponents"));
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
		$this->_element->setAttribute('target_id', $id);
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
		foreach (array_keys($possibleDestinations) as $id) {
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
}

?>