<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlFixedOrganizerSiteComponent.class.php,v 1.7 2006/04/12 21:07:16 adamfranco Exp $
 */ 

/**
 * The XML site nav block component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlFixedOrganizerSiteComponent.class.php,v 1.7 2006/04/12 21:07:16 adamfranco Exp $
 */
class XmlFixedOrganizerSiteComponent
	extends XmlOrganizerSiteComponent 
	// implements FixedOrganizerSiteComponent
{

	/**
	 * Answer a displayName for this organizer. (Generally, a type or classification).
	 * 
	 * @return string
	 * @access public
	 * @since 4/10/06
	 */
	function getDisplayName () {
		return _("<em>Fixed Organizer</em>");
	}
	
	/**
	 * Add a subcomponent to an empty cell
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param integer $cellIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function addSubcomponentToCell ( &$siteComponent, $cellIndex ) {
		$child =& $this->_element->firstChild;
		$i = 0;
		$success = false;
		while ($child) {
			// is the cell we want, is empty
			if ($i == $cellIndex) {
				if (!$child->hasChildNodes()) {
					$child->appendChild($siteComponent->getElement());
					$success = true;
				} else
					throwError( new Error("Cell Not Empty", "SiteComponents"));
			} else {
				$child =& $child->nextSibling;
				$i++;
			}
		}
		if (!$success)
			throwError( new Error("Cell $cellIndex Not Found", "SiteComponents"));
	}
	
	/**
	 * Put a subcomponent in a given cell if at all possible. If the subcomponent
	 * is in the organizer, then move/swap-positions/etc to get it there. If is is not,
	 * add it to the organizer, then move it to that position
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param integer $cellIndex
	 * @return void
	 * @access public
	 * @since 4/12/06
	 */
	function putSubcomponentInCell ( &$siteComponent, $cellIndex ) {
		$currentIndex = $this->getCellForSubcomponent($siteComponent);
		if ($currentIndex === FALSE) {
			$oldParent =& $siteComponent->getParentComponent();
			$oldParent->detatchSubcomponent($siteComponent);
			$this->addSubcomponentToCell($siteComponent, $cellIndex);
		} else
			$this->swapCells($currentIndex, $cellIndex);
	}
	
	/**
	 * Swap the contents of two cells
	 * 
	 * @param integer $cellOneIndex
	 * @param integer $cellTwoIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function swapCells ( $cellOneIndex, $cellTwoIndex ) {
		// child DOMIT_Elements in an array
		$children =& $this->_element->childNodes;
		// components for cells
		$cell_one_component = $this->getSubcomponentForCell($cellOneIndex);
		$cell_two_component = $this->getSubcomponentForCell($cellTwoIndex);
		
		// third party (temp for a swap)
		$temp =& $cell_two_component->getElement;
		$children[$cellTwoIndex]->replaceChild(
										$cell_one_component->getElement(),
										$cell_two_component->getElement());
		$children[$cellOneIndex]->replaceChild(
										$temp,
										$cell_one_component->getElement());
	}
	
	/**
	 * Answer the ordered indices.
	 * 
	 * Currently Ignoring Direction and assuming left-right/top-bottom
	 * @return array
	 * @access public
	 * @since 4/3/06
	 */
	function getVisibleOrderedIndices () {
		$rows = $this->getNumRows();
		$cols = $this->getNumColumns();
		$array = array();
		for ($i = 0; $i < $rows*$cols; $i++) {
			$array[] = $i;
		}
		return $array;
	}

	/**
	 * Answer the number of cells in this organizer that are visible (some may
	 * be empty).
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getNumberOfVisibleCells () {
		return $this->_getTotalNumberOfCells();
	}
		
	/**
	 * Delete the subcomponent located in organizer cell $i
	 * 
	 * @param integer $i
	 * @return void
	 * @access public
	 * @since 4/3/06
	 */
	function deleteSubcomponentInCell ( $i ) {
		$cell =& $this->_element->firstChild;
		while ($i) {
			$cell =& $cell->nextSibling;
			$i--;
		}
		$cell->removeChild($cell->firstChild);
		$this->_director->deleteSiteComponent($this->getSubcomponentForCell($i));
		unset($this->_childComponents);
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
		return $visitor->visitFixedOrganizer($this);
	}
	
/*********************************************************
 * Drag & Drop destinations
 *********************************************************/
	
	/**
	 * Answer an array of the components that could possibly be added to this organizer.
	 * 
	 * @param integer $cellIndex
	 * @return ref array An array keyed by component Id
	 * @access public
	 * @since 4/11/06
	 */
	function &getVisibleComponentsForPossibleAdditionToCell ( $cellIndex ) {
		// If this cell is in use, only reordering of components already
		// in this organizer is allowed (FixedOrganizer)
		if (in_array($this->getId().'_cell:'.$cellIndex, $this->_director->getFilledTargetIds())
			|| is_object($this->getSubcomponentForCell($cellIndex)))
		{
			return $this->getSubcomponentsNotInCell($cellIndex);
		}
		// If it is empty, then our current subcomponents or any other available
		// can be added.
		else {
			return parent::getVisibleComponentsForPossibleAdditionToCell($cellIndex);
		}
	}
	
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
		$parent =& $this->getParentComponent();
		return $parent->getParentNavOrganizer();
	}
	
	/**
	 * Answer the FixedOrganizers below this organizer
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/11/06
	 */
	function &getFixedOrganizers () {
		$results = array();
		
		$children =& $this->_getChildComponents();
		foreach (array_keys($children) as $key) {
			if (strtolower("XmlFixedOrganizerSiteComponent") == strtolower(get_class($children[$key])))
				$results[$children[$key]->getId()] =& $children[$key];
		}
		
		return $results;
	}
}

?>