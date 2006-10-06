<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetFlowOrganizerSiteComponent.class.php,v 1.3 2006/10/06 15:43:09 adamfranco Exp $
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
 * @version $Id: AssetFlowOrganizerSiteComponent.class.php,v 1.3 2006/10/06 15:43:09 adamfranco Exp $
 */
class AssetFlowOrganizerSiteComponent
	extends AssetOrganizerSiteComponent 
	// implements FlowOrganizerSiteComponent
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
		$this->updateNumRows(0);
		$this->updateNumColumns(1);
		$this->updateOverflowStyle('Paginate');
	}
	
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
		$this->_saveXml();
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
		
		$this->_saveXml();
		
		if ($this->_director->NavBlockType->isEqual($siteComponent->_asset->getAssetType())
			|| $this->_director->BlockType->isEqual($siteComponent->_asset->getAssetType()))
		{
			$this->_asset->addAsset($siteComponent->_asset->getId());
		}
	}
	
	/**
	 * Add a subcomponent to a given cell and push the later elements towards the
	 * end.
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param integer $cellIndex
	 * @return string The Id of the original cell
	 * @access public
	 * @since 3/31/06
	 */
	function putSubcomponentInCell ( &$siteComponent, $cellIndex ) {
		$currentIndex = $this->getCellForSubcomponent($siteComponent);
		if ($currentIndex === FALSE) {			
			// A cell will have no old parent if it is newly created.
			if ($oldParent =& $siteComponent->getParentComponent()) {
				$oldCellId = $oldParent->getId()."_cell:".$oldParent->getCellForSubcomponent($siteComponent);
				$oldParent->detatchSubcomponent($siteComponent);
			} else {
				$oldCellId = null;
			}
			
			$this->addSubcomponent($siteComponent);
			$currentIndex = $this->getCellForSubcomponent($siteComponent);
		} else {
			$oldCellId = $this->getId()."_cell:".$currentIndex;
		}
		
		$this->moveBefore($currentIndex, $cellIndex);
		
		return $oldCellId;
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
		
		$this->_saveXml();
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
		
		$this->_saveXml();
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
		$this->_element->removeChild($cell);
		
		$this->_saveXml();
		
		if ($this->_director->NavBlockType->isEqual($subcomponent->_asset->getAssetType())
			|| $this->_director->BlockType->isEqual($subcomponent->_asset->getAssetType()))
		{
			$this->_asset->removeAsset($subcomponent->_asset->getId(), true);
		}
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
		return $visitor->visitFlowOrganizer($this);
	}
	
	/**
	 * Answer true if there is a level of menus below the current one.
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/22/06
	 */
	function subMenuExists () {
		// Flow organizers can't contain menus.
		return false;
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