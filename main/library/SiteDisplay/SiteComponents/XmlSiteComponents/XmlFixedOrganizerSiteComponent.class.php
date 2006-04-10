<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlFixedOrganizerSiteComponent.class.php,v 1.4 2006/04/10 19:51:20 adamfranco Exp $
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
 * @version $Id: XmlFixedOrganizerSiteComponent.class.php,v 1.4 2006/04/10 19:51:20 adamfranco Exp $
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
}

?>