<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlOrganizerSiteComponent.class.php,v 1.15 2006/09/18 14:51:10 adamfranco Exp $
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
 * @version $Id: XmlOrganizerSiteComponent.class.php,v 1.15 2006/09/18 14:51:10 adamfranco Exp $
 */
class XmlOrganizerSiteComponent
	extends XmlSiteComponent
	// implements OrganizersiteComponent
{

	/**
	 * Populate this object with default values
	 * 
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function populateWithDefaults () {
		$this->updateNumRows(1);
		$this->updateNumColumns(1);
		$this->updateDirection('Left-Right/Top-Bottom');
	}
	
	/**
	 * Answer a displayName for this organizer. (Generally, a type or classification).
	 * 
	 * @return string
	 * @access public
	 * @since 4/10/06
	 */
	function getDisplayName () {
		return _("Organizer");
	}
	
	/**
	 * @var array $_childComponents;  
	 * @access private
	 * @since 4/4/06
	 */
	var $_childComponents = null;
	
	/**
	 * Answer the number of rows.
	 * 
	 * @return integer
	 * @access public
	 * @since 4/3/06
	 */
	function getNumRows () {
		if ($this->_element->hasAttribute('rows'))
			return $this->_element->getAttribute('rows');
		return 0;
	}
	
	/**
	 * Update the number of rows. The contents of this organizer may limit the
	 * ability to reduce the number of rows.
	 * 
	 * @param integer $newRows
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateNumRows ( $newRows ) {
		$this->_element->setAttribute('rows', $newRows);
	}

	/**
	 * Answer the number of columns.
	 * 
	 * @return integer
	 * @access public
	 * @since 4/3/06
	 */
	function getNumColumns () {
		if ($this->_element->hasAttribute('cols') && intval($this->_element->getAttribute('cols')) > 0)
			return intval($this->_element->getAttribute('cols'));
		return 1;
	}
	
	/**
	 * Update the number of columns. The contents of this organizer may limit the
	 * ability to reduce the number of columns.
	 * 
	 * @param integer $newColumns
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateNumColumns ( $newColumns ) {
		$this->_element->setAttribute('cols', $newColumns);
	}
	
	/**
	 * Answer the total number of cells in this organizer. (Some may be empty)
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getTotalNumberOfCells () {
		return $this->_element->childCount;
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
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the direction of indexing:
	 * 		Left-Right/Top-Bottom
	 *		Top-Bottom/Left-Right
	 *		Right-Left/Top-Bottom
	 *		Top-Bottom/Right-Left
	 * 		Left-Right/Bottom-Top
	 *		Bottom-Top/Left-Right
	 *		Right-Left/Bottom-Top
	 *		Bottom-Top/Right-Left
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getDirection () {
		if ($this->_element->hasAttribute('direction'))
			return $this->_element->getAttribute('direction');
		return 'Left-Right/Top-Bottom'; // the default direction
	}
	
	/**
	 * Update the direction of indexing:
	 * 		Left-Right/Top-Bottom
	 *		Top-Bottom/Left-Right
	 *		Right-Left/Top-Bottom
	 *		Top-Bottom/Right-Left
	 * 		Left-Right/Bottom-Top
	 *		Bottom-Top/Left-Right
	 *		Right-Left/Bottom-Top
	 *		Bottom-Top/Right-Left
	 * 
	 * @param string $direction
	 * @access public
	 * @since 3/31/06
	 */
	function updateDirection ( $direction ) {
		$this->_element->setAttribute('direction', $direction);
	}
	
	/**
	 * Answer the subcomponent located in organizer cell $i
	 * 
	 * @param integer $i
	 * @return object SiteComponent OR null if not found
	 * @access public
	 * @since 4/3/06
	 */
	function &getSubcomponentForCell ( $i ) {
		$childComponents =& $this->_getChildComponents();

		// return the subcomponent or null
		if (isset($childComponents[$i])) {
			return $childComponents[$i];
		} else {
			$null = null;
			return $null;
		}
	}
	
	/**
	 * Answer the index of the component passed.
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return integer FALSE if not found
	 * @access public
	 * @since 4/12/06
	 */
	function getCellForSubcomponent ( &$siteComponent ) {
		$childComponents =& $this->_getChildComponents();
		foreach($childComponents as $index => $component) {
			if (is_object($component) && $component->getId() == $siteComponent->getId())
				return $index;
		}
		
		// if not found, return false
		return false;
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
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Remove a subcomponent, but don't delete it from the director completely.
	 * 
	 * @param object SiteComponent $subcomponent
	 * @return void
	 * @access public
	 * @since 4/12/06
	 */
	function detatchSubcomponent ( &$subcomponent ) {
		$cellIndex = $this->getCellForSubcomponent($subcomponent);
		
		$cell =& $this->_element->firstChild;
		while ($cellIndex) {
			$cell =& $cell->nextSibling;
			$cellIndex--;
		}
		$cell->removeChild($subcomponent->getElement());
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
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}	
	
	/**
	 * Load the child elements into an array from the data source
	 * 
	 * @param boolean $force true if forcing repopulation (testing)
	 * @return ref array
	 * @access public
	 * @since 4/4/06
	 */
	function &_getChildComponents ($force = false) {
		// load the data array
		if ($force || !is_array($this->_childComponents)) {
			$this->_childComponents = array();
			
			$child =& $this->_element->firstChild;
			while ($child) {
				if ($child->nodeName == 'cell') {
					if ($child->firstChild) {
						$this->_childComponents[] =& $this->_director->getSiteComponent($child->firstChild);
					} else {
						$this->_childComponents[] = null;
					}
				}
				$child =& $child->nextSibling;
			}
		}
		return $this->_childComponents;
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
		// merge our current subcomponents and any other component to be
		// added. (This is for the Flow/Menu organizers, as well as the
		// empty case for Fixed organizers.
		$addableComponents =& $this->getVisibleComponentsForPossibleAddition();
		$movableSubcomponents =& $this->getSubcomponentsNotInCell($cellIndex);
		$results = array();
		foreach (array_keys($addableComponents) as $id)
			$results[$id] =& $addableComponents[$id];
		foreach (array_keys($movableSubcomponents) as $id)
			$results[$id] =& $movableSubcomponents[$id];
		return $results;
		
	}
	
	/**
	 * Answer an array of  the subcomponents not in the given cell
	 * 
	 * @param integer $i
	 * @return ref array Array of component objects keyed by Id
	 * @access public
	 * @since 4/11/06
	 */
	function &getSubcomponentsNotInCell ($i) {
		$childComponents =& $this->_getChildComponents();
		$results = array();
		foreach (array_keys($childComponents) as $index) {
			if ($index != $i && is_object($childComponents[$index]))
				$results[$childComponents[$index]->getId()] =& $childComponents[$index];
		}
		return $results;
	}
	
	/**
	 * Answer an array of the components that could possibly be added to this organizer.
	 * 
	 * @return ref array An array keyed by component Id
	 * @access public
	 * @since 4/11/06
	 */
	function &getVisibleComponentsForPossibleAddition () {
		$results = array();
		
		// If not authorized to add children, return an empty array;
		// @todo
		if(false) {
			return $results;
		}
		
		$visibleComponents =& $this->_director->getVisibleComponents();
		foreach (array_keys($visibleComponents) as $id) {
			$possibleDestinations =& $visibleComponents[$id]->getVisibleDestinationsForPossibleAddition();
			foreach (array_keys($possibleDestinations) as $destId) {
				if ($destId == $this->getId()) {
					$results[$id] =& $visibleComponents[$id];
					break;
				}
			}
		}
		
		return $results;
	}
}

?>