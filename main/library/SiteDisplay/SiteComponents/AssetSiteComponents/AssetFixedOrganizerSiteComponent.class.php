<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetFixedOrganizerSiteComponent.class.php,v 1.13 2008/01/23 22:06:53 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/../AbstractSiteComponents/FixedOrganizerSiteComponent.abstract.php");


/**
 * The XML site nav block component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetFixedOrganizerSiteComponent.class.php,v 1.13 2008/01/23 22:06:53 adamfranco Exp $
 */
class AssetFixedOrganizerSiteComponent
	extends AssetOrganizerSiteComponent 
	implements FixedOrganizerSiteComponent
{

	/**
	 * Answer a displayName for this organizer. (Generally, a type or classification).
	 * 
	 * @return string
	 * @access public
	 * @since 4/10/06
	 */
	function getDisplayName () {
		return _("<span class='controls_bar_title'>Layout Container</span>");
	}
	
	/**
	 * Answer the component class
	 * 
	 * @return string
	 * @access public
	 * @since 11/09/07
	 */
	function getComponentClass () {
		return "FixedOrganizer";
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
	public function addSubcomponentToCell ( SiteComponent $siteComponent, $cellIndex ) {
		$this->normalizeCells();
		
		$child = $this->_element->firstChild;
		$i = 0;
		$success = false;
		while ($child && !$success) {			
			// is the cell we want, is empty
			if ($i == $cellIndex) {
				if (!$child->hasChildNodes()) {
					try {
						$child->appendChild($siteComponent->getElement());
					} catch (DOMException $e) {
						// Wrong Document Error
						if ($e->getCode() == 4) {
							$oldElement = $siteComponent->getElement();
							$newElement = $child->ownerDocument->importNode($oldElement, true);
							$siteComponent->_element = $newElement;
							$child->appendChild($newElement);
						} else {
							throw $e;
						}
					}
					$success = true;
				} else {
					throwError( new Error("Cell Not Empty", "SiteComponents"));
				}
			} else {
				$child = $child->nextSibling;
				$i++;
			}
		}
		
		if (!$success)
			throwError( new Error("Cell $cellIndex Not Found", "SiteComponents"));
		
		$this->_saveXml();
		
		// Ensure that any assets referenced in the XML are added to our asset.
		$childAssetIdsBelowSubcomponent = $this->_getAssetIdsBelowElement(
			$siteComponent->getElement());
		$idManager = Services::getService('Id');
		foreach ($childAssetIdsBelowSubcomponent as $idString) {
			$this->_asset->addAsset($idManager->getId($idString));
		}
	}
	
	/**
	 * Put a subcomponent in a given cell if at all possible. If the subcomponent
	 * is in the organizer, then move/swap-positions/etc to get it there. If is is not,
	 * add it to the organizer, then move it to that position
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param integer $cellIndex
	 * @return string The Id of the original cell
	 * @access public
	 * @since 4/12/06
	 */
	function putSubcomponentInCell ( SiteComponent $siteComponent, $cellIndex ) {
		$this->normalizeCells();
		$currentIndex = $this->getCellForSubcomponent($siteComponent);
		if ($currentIndex === FALSE) {
			// A cell should not have no old parent if it is newly created.
			if ($oldParent = $siteComponent->getParentComponent()) {
				if (method_exists($oldParent, 'getCellForSubcomponent'))
					$oldCellId = $oldParent->getId()."_cell:".$oldParent->getCellForSubcomponent($siteComponent);
				else 
					$oldCellId = null;
				
				// If the siteComponent reports a parent, but really has not been
				// added as an xml child node of the parent, continue
				try {
					$oldParent->detatchSubcomponent($siteComponent);
				} catch (DOMException $e) {
					$oldCellId = null;
				}
			} else {
				$oldCellId = null;
			}
			
			$this->addSubcomponentToCell($siteComponent, $cellIndex);
			
			return $oldCellId;
		} else {
			$this->swapCells($currentIndex, $cellIndex);
			
			return $this->getId()."_cell:".$currentIndex;
		}
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
		$this->normalizeCells();
		
        // child DOMElements in an array
        $children = $this->_element->childNodes;
        // cells
        $cell_one = $children->item($cellOneIndex);
        $cell_two = $children->item($cellTwoIndex);
        $temp = $this->_element->ownerDocument->createElement('temp');
        
        $this->_element->replaceChild($temp, $cell_one);
        $this->_element->replaceChild($cell_one, $cell_two);
        $this->_element->replaceChild($cell_two, $temp);
       
       	$this->_saveXml();
	}
	
	/**
	 * Update the number of rows. The contents of this organizer may limit the
	 * ability to reduce the number of rows.
	 * 
	 * @param integer $newRows
	 * @param boolean $initialCreation To prevent loops, set to true when first
	 *									creating this component.
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateNumRows ( $newRows, $initialCreation = FALSE ) {
		if (!$initialCreation && ($newRows * $this->getNumColumns()) < $this->getMinNumCells())
			throw new OperationFailedException("New number ($newRows) of rows will result in less than the needed number of cells.");
		
		parent::updateNumRows($newRows);
		
		$this->normalizeCells($initialCreation);
	}
	
	/**
	 * Update the number of columns. The contents of this organizer may limit the
	 * ability to reduce the number of columns.
	 * 
	 * @param integer $newColumns
	 * @param boolean $initialCreation To prevent loops, set to true when first
	 *									creating this component.
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateNumColumns ( $newColumns, $initialCreation = FALSE ) {
		if (!$initialCreation && ($newColumns * $this->getNumRows()) < $this->getMinNumCells())
			throw new OperationFailedException("New number ($newColumns) of columns will result in less than the needed number of cells.");
		
		parent::updateNumColumns($newColumns);
		
		$this->normalizeCells($initialCreation);	
	}
	
	/**
	 * Populate the <cell/> tags to fit our rows/columns
	 * 
	 * @param boolean $initialCreation To prevent loops, set to true when first
	 *									creating this component.
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function normalizeCells ($initialCreation = FALSE) {
		$numCells = $this->getNumColumns() * $this->getNumRows();
		
		// Add new cells up to our number
		for ($i = $this->_element->childNodes->length; $i < $numCells; $i++) {
			$this->_element->appendChild(
				$this->_element->ownerDocument->createElement('cell'));
		}
		
		if (!$initialCreation) {
			// Remove tags after the max shown if needed.
			$lastUsed = $this->getLastIndexFilled();
			for ($i = $this->_element->childNodes->length - 1; $i >= $numCells; $i--) {
				if ($i > $lastUsed)
					$this->_element->removeChild($this->_element->childNodes->item($i));
			}
		}
		
		$this->_saveXml();
	}
	
	/**
	 * Answer the total number of cells in this organizer. (Some may be empty) 
	 * Cells are indexed from zero
	 * 
	 * @return integer
	 * @access public
	 * @since 9/18/06
	 */
	function getTotalNumberOfCells () {
		 return $this->getNumRows()*$this->getNumColumns();
	}
	
	/**
	 * Answer true if this organizer is used as a target for a menu.
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/11/08
	 */
	public function isMenuTarget () {
		$filledTargetCells = $this->_director->getFilledTargetIds($this->getId());
		$myFilledTargetCells = array();
		foreach ($filledTargetCells as $cellId) {
			if (preg_match('/'.$this->getId().'_cell:([0-9]+)/', $cellId, $matches)) {
				return true;
			}
		}
		
		return false;
	}
	
	/**
	 * Answer the last-used index
	 * 
	 * @return integer
	 * @access public
	 * @since 4/17/06
	 */
	function getLastIndexFilled () {
		$filledTargetCells = $this->_director->getFilledTargetIds($this->getId());
		$myFilledTargetCells = array();
		foreach ($filledTargetCells as $cellId) {
			if (preg_match('/'.$this->getId().'_cell:([0-9]+)/', $cellId, $matches)) {
				$myFilledTargetCells[] = intval($matches[1]);
			}
		}
				
		for ($i = $this->_element->childNodes->length - 1; $i >= 0; $i--) {
			if (in_array($i, $myFilledTargetCells))
				return $i;
				
			if ($this->_element->childNodes->item($i)->firstChild)
				return $i;
		}
		return false;
	}
	
	/**
	 * Answer the minimum number of cells that this organizer can have.
	 *
	 * 
	 * @return integer
	 * @access public
	 * @since 7/28/06
	 */
	function getMinNumCells () {
		return $this->getLastIndexFilled() + 1;
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
		$cell = $this->_element->firstChild;
		while ($i) {
			$cell = $cell->nextSibling;
			$i--;
		}
		$cell->removeChild($cell->firstChild);
		$this->_director->deleteSiteComponent($this->getSubcomponentForCell($i));
		unset($this->_childComponents);
		
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
	function getVisibleComponentsForPossibleAdditionToCell ( $cellIndex ) {
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
		
		// As are FixedOrganizers that are below the parent NavOrganizer, but
		// not below me.
		$parentNavsFixedOrganizers = $parentNav->getFixedOrganizers();
		
		$myFixedOrganizerIds = array_keys($this->getFixedOrganizers());
		
		foreach (array_keys($parentNavsFixedOrganizers) as $id) {
			if ($id != $this->getId() && !in_array($id, $myFixedOrganizerIds))
				$results[$id] = $parentNavsFixedOrganizers[$id];
		}
		
		return $results;
	}
	
	/**
	 * Answer the FixedOrganizers below this organizer
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/11/06
	 */
	function getFixedOrganizers () {
		$results = array();
		
		$children = $this->_getChildComponents();
		foreach (array_keys($children) as $key) {
// 			if ($children[$key]) {
// 				print "<hr/>";
// 				printpre($children[$key]->getId());
// 				printpre(get_class($children[$key]));
// 			}
			if (is_object($children[$key]) && preg_match('/^.*FixedOrganizerSiteComponent$/i', get_class($children[$key]))) 
			{
				$results[$children[$key]->getId()] = $children[$key];
				
			}
		}
		
		return $results;
	}
	
	/**
	 * Answer true if there is a level of menus below the current one.
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/22/06
	 */
	function subMenuExists () {
		// Check all children, Menu Organizers will return true.
		$children = $this->_getChildComponents();
		foreach (array_keys($children) as $key) {
			if (is_object($children[$key]) && $children[$key]->subMenuExists())
				return TRUE;
		}
		
		return false;
	}
}

?>