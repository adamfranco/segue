<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlFlowOrganizerSiteComponent.class.php,v 1.8 2006/04/11 21:06:25 adamfranco Exp $
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
 * @version $Id: XmlFlowOrganizerSiteComponent.class.php,v 1.8 2006/04/11 21:06:25 adamfranco Exp $
 */
class XmlFlowOrganizerSiteComponent
	extends XmlOrganizerSiteComponent 
	// implements FlowOrganizerSiteComponent
{
	
	/**
	 * Answer a displayName for this organizer. (Generally, a type or classification).
	 * 
	 * @return string
	 * @access public
	 * @since 4/10/06
	 */
	function getDisplayName () {
		return _("<em>Content Organizer</em>");
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
		$array = array();
		for ($i = 0; $i < $this->getNumberOfVisibleCells(); $i++) {
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
		$max = $this->getMaxVisible();
		return (!$max || $this->_element->childCount < $max)
			?$this->_element->childCount:$max;
	}
	
	/**
	 * Answer the maximum number of cells that can be displayed before overflowing
	 * (i.e. to pagination, archiving, hiding, etc).
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getMaxVisible () {
		if ($this->_element->hasAttribute("maxVisible"))
			return $this->_element->getAttribute("maxVisible");
		else
			return 0;
	}
	
	/**
	 * Update the maximum number of cells that can be displayed before overflowing
	 * (i.e. to pagination, archiving, hiding, etc).
	 * 
	 * @param integer $newMaxVisible Greater than or equal to 1
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateMaxVisible ( $newMaxVisible ) {
		$this->_element->setAttribute('maxVisible', $newMaxVisible);
	}
	
	/**
	 * Get the overflow style:
	 *		Paginate
	 *		Archive
	 *		Hide
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getOverflowStyle () {
		if ($this->_element->hasAttribute("overflowStyle"))
			return $this->_element->getAttribute("overflowStyle");
		return "Paginate";
	}
	
	/**
	 * Update the overflow style:
	 *		Paginate
	 *		Archive
	 *		Hide
	 * 
	 * @param string $overflowStyle
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateOverflowStyle ( $overflowStyle ) {
		$this->_element->setAttribute("overflowStyle", $overflowStyle);
	}
	
	/**
	 * Add a subcomponent
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function addSubcomponent ( &$siteComponent ) {
		$cell =& $this->_element->ownerDocument->createElement('cell');
		$cell->appendChild($siteComponent->getElement());
		$this->_element->appendChild($cell);
		// this is only for single page load deletes (testing)
		$this->_getChildComponents(true);
	}
	
	/**
	 * Move the contents of cellOneIndex before cellTwoIndex
	 * 
	 * @param integer $cellOneIndex
	 * @param integer $cellTwoIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function moveBefore ( $cellOneIndex, $cellTwoIndex ) {
		// child DOMIT_Elements in an array
		$children =& $this->_element->childNodes;

		$temp =& $children[$cellOneIndex];

		$this->_element->removeChild($children[$cellOneIndex]);
		
		// indices change when child is removed in front of cellTwoIndex
		if ($cellTwoIndex > $cellOneIndex)
			$this->_element->insertBefore($temp, $children[$cellTwoIndex - 1]);
		else
			$this->_element->insertBefore($temp, $children[$cellTwoIndex]);
	}
	
	/**
	 * Move the contents of cellIndex to the end of the organizer
	 * 
	 * @param integer $cellIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function moveToEnd ( $cellIndex ) {
		$temp =& $this->_element->childNodes[$cellIndex];
		$this->_element->removeChild($temp);
		$this->_element->appendChild($temp);
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
		$this->_element->removeChild($cell);
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
		return $visitor->visitFlowOrganizer($this);
	}
	
/*********************************************************
 * Drag & Drop destinations
 *********************************************************/
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
	 *
	 * For flow organizers the possible destinations are cells in any
	 * FixedOrganizer or NavOrganizer
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
		
		
		$visibleComponents =& $this->_director->getVisibleComponents();
		foreach (array_keys($visibleComponents) as $id) {
			if (strtolower("XmlFixedOrganizerSiteComponent") == strtolower(get_class($visibleComponents[$id]))
				|| strtolower("XmlNavOrganizerSiteComponent") == strtolower(get_class($visibleComponents[$id])))
			{
					$results[$id] =& $visibleComponents[$id];
			}
		}
		
		return $results;
	}
	
}

?>