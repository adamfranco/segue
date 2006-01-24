<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavigationNodeRenderer.class.php,v 1.7 2006/01/24 17:59:51 adamfranco Exp $
 */
 
require_once(HARMONI."GUIManager/Components/MenuItemLinkWithAdditionalHtml.class.php");

/**
 * The NodeRenderer class takes an Asset and renders its navegational item,
 * as well as its children if selected
 * 
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavigationNodeRenderer.class.php,v 1.7 2006/01/24 17:59:51 adamfranco Exp $
 */
class NavigationNodeRenderer
	extends NodeRenderer
{

/*********************************************************
 * Object Properties
 *********************************************************/
	/**
	 * @var string $_layoutArrangement;  
	 * @access private
	 * @since 1/19/06
	 */
	var $_layoutArrangement;
	
	/**
	 * @var integer $_numCells;  
	 * @access private
	 * @since 1/19/06
	 */
	var $_numCells;
	
	/**
	 * @var integer $_targetOverride;  
	 * @access private
	 * @since 1/19/06
	 */
	var $_targetOverride;

/*********************************************************
 * Instance Methods
 *********************************************************/
	
	/**
	 * Answer the GUI component for the navegational item.
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderNavComponent ($level = 1) { 
		$component =& new MenuItemLinkWithAdditionalHtml(
						$this->_asset->getDisplayName(), 
						$this->getMyUrl(), 
						$this->_active,
						$level,
						null,
						null,
						null,
						$this->getSettingsForm());
						
		return $component;
	}
	
	/**
	 * Answer the GUI component for target area
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderTargetComponent ($level = 1) {
		$layoutArrangement = $this->getLayoutArrangement();
		$numCells = $this->getNumCells();
		$targetOverride = $this->getTargetOverride();
		if (!$targetOverride || $targetOverride > $numCells)
			throwError(new Error("$targetOverride overflows number of cells, $numCells.", __FILE__, TRUE));		
		$xLayout =& new XLayout();
		$yLayout =& new YLayout();
		$children =& $this->getOrderedChildren();
		
		// In single-cell arrangement, each child will be given its own
		// target with which it can subdivide if necessary for any of its
		// children.
		if ($numCells <= 1) {
			$container =& new Container($yLayout, BLANK, 1);
			for ($i = 0; $i < count($children); $i++) {
				$childRenderer =& NodeRenderer::forAsset($children[$i], $this);
				
				// print a heading if availible
				if ($childRenderer->getTitle()) {
					$container->add(
							new Heading($childRenderer->getTitle(), 2),
							null, null, CENTER, TOP);
				}
				
				// print the content
				$container->add(
						$childRenderer->renderTargetComponent(),
						null, null, CENTER, TOP);
			}
		}
		
		// In multi-cell arrangements the child navigational components will
		// be rendered in all cells but the one designated as the 'target'.
		// The 'active' child is given the 'target' cell in which to render
		// its children.
		else {
		
			// Make our container
			if ($layoutArrangement == 'columns') {
				$layout =& $xLayout;
				$cellLayout =& $yLayout;
				$cellWidth = '250px';
				$cellHeight = NULL;
			} else {
				$layout =& $yLayout;
				$cellLayout =& $xLayout;
				$cellWidth = NULL;
				$cellHeight = NULL;
			}
			$container =& new Container($layout, BLANK, 1);
			
			
			// Add our cells
			$cells = array();
			$overallCellNumber = 1;
			$cellIndex = 1;
			while ($overallCellNumber <= $numCells) {
				if ($overallCellNumber == $targetOverride) {
					$targetCell =& $container->add(
								new Container($yLayout, BLANK, 1), 
								NULL, $cellHeight, CENTER, TOP);
				} else {
					$cells[$cellIndex] =& $container->add(
								new Menu($cellLayout, $level), 
								$cellWidth, $cellHeight, CENTER, TOP);
					$cellIndex++;
				}
				$overallCellNumber++;
			}
			
			
			// Add our children to our cells
			if (!count($children)) {
				for ($i = 1; $i < $numCells; $i++) {
					$cells[$i]->add(
						new MenuItem("[debug: no children]<br/>[Cell[$i]]", 
							$level),
						null, null, CENTER, TOP);
				}
				
				$targetCell->add(
					new Block("[debug: no children]<br/>[Cell[$i]=target]", 
						EMPHASIZED_BLOCK),
					null, null, CENTER, TOP);
			} else {
				for ($i = 0; $i < count($children); $i++) {
					$childRenderer =& NodeRenderer::forAsset($children[$i], $this);
					$childCell = $this->getDestinationForAsset($children[$i]);
					
					$cells[$childCell]->add(
						$childRenderer->renderNavComponent(),
						null, null, CENTER, TOP);
					
					if ($childRenderer->isActive()) {
						$targetCell->add(
							$childRenderer->renderTargetComponent(),
							null, null, CENTER, TOP);
					}
				}
			}
		}
		
		return $container;
	}
	
	/**
	 * Answer an ordered array of children
	 * 
	 * @return array
	 * @access public
	 * @since 1/20/06
	 */
	function &getOrderedChildren () {
		if (!isset($this->_orderedChildren)) {
			$orderedChildren = array();
			$orderedChildIds = array();
			$unorderedChildren = array();
			$unorderedChildIds = array();
			$children =& $this->_asset->getAssets();
			while ($children->hasNext()) {
				$child =& $children->next();
				$childId =& $child->getId();
				
				$key = array_search($childId->getIdString(), $this->_childOrder);
				if ($key !== false) {
					$orderedChildren[$key] =& $child;
					$orderedChildIds[$key] = $childId->getIdString();
				} else {
					$unorderedChildren[] =& $child;
					$unorderedChildIds[] = $childId->getIdString();
				}
			}
			ksort($orderedChildren);
			for ($i = 0; $i < count($unorderedChildren); $i++) {
				$orderedChildren[] =& $unorderedChildren[$i];
				$orderedChildIds[] =& $unorderedChildIds[$i];
			}
			
			if (count($unorderedChildren))
				$this->_updateChildOrder($orderedChildren);
				
			$this->_orderedChildren =& $orderedChildren;
			$this->_orderedChildIds =& $orderedChildIds;
		}		
		return $this->_orderedChildren;
	}
	
	/**
	 * Answer an ordered array of child Ids
	 * 
	 * @return array
	 * @access public
	 * @since 1/23/06
	 */
	function getOrderedChildIds () {
		if (!isset($this->_orderedChildIds))
			$this->getOrderChildren();
		return $this->_orderedChildIds;
	}
	
	/**
	 * Answer the destination cell of a child Id
	 * 
	 * @param object Id $childId
	 * @return integer
	 * @access public
	 * @since 1/23/06
	 */
	function getDestinationCell ( &$childId ) {
		$idString = $childId->getIdString();
		foreach($this->_childCells as $cell => $cellList) {
			if (array_search($idString, $cellList) !== false)
				return $cell;
		}
		// default to 1
		return 1;
	}
	
	/**
	 * Answer the layout arrangement
	 * 
	 * @return string
	 * @access public
	 * @since 1/19/06
	 */
	function getLayoutArrangement () {
		if (!$this->_layoutArrangement)
			$this->_loadNavRecord();
		
		return $this->_layoutArrangement;
	}
	
	/**
	 * Answer the number of cells for this node
	 * 
	 * @return integer
	 * @access public
	 * @since 1/19/06
	 */
	function getNumCells () {
		if (!$this->_numCells)
			$this->_loadNavRecord();
		
		return $this->_numCells;
	}
	
	/**
	 * Answer cell number to be used as a target for children
	 * 
	 * @return integer
	 * @access public
	 * @since 1/19/06
	 */
	function getTargetOverride () {
		if (!$this->_targetOverride)
			$this->_loadNavRecord();
		
		return $this->_targetOverride;
	}
	
	/**
	 * Answer the desired cell in which to place the asset's navegation component
	 * 
	 * @param object Asset $asset
	 * @return integer
	 * @access public
	 * @since 1/19/06
	 */
	function getDestinationForAsset ( &$asset ) {
		// @todo implement
		return 1;
	}
	
/*********************************************************
 * Instance Methods - Private
 *********************************************************/
	
	/**
	 * Load the Navigational information from the asset
	 * 
	 * @return void
	 * @access private
	 * @since 1/19/06
	 */
	function _loadNavRecord () {
		$idManager =& Services::getService("Id");
		
		// Get the nav info
		$navRecords =& $this->_asset->getRecordsByRecordStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs'));
		$navRecord =& $navRecords->next();
		
		
		$parts =& $navRecord->getPartsByPartStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.layout_arrangement'));
		$part =& $parts->next();
		$value =& $part->getValue();
		$this->_layoutArrangement = $value->asString();
		
		
		$parts =& $navRecord->getPartsByPartStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.num_cells'));
		$part =& $parts->next();
		$value =& $part->getValue();
		$this->_numCells = $value->value();
		
		
		$parts =& $navRecord->getPartsByPartStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.target_override'));
		$part =& $parts->next();
		$value =& $part->getValue();
		$this->_targetOverride = $value->value();
		
		
		$parts =& $navRecord->getPartsByPartStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.child_order'));
		if ($parts->hasNext()) {
			$part =& $parts->next();
			$value =& $part->getValue();
			$this->_childOrder = explode("\t", $value->asString());
		} else {
			$this->_childOrder = array();
		}
		
		$parts =& $navRecord->getPartsByPartStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.child_cells'));
		$this->_childCells = array();
		if ($parts->hasNext()) {
			$part =& $parts->next();
			$value =& $part->getValue();
			$storedCells = explode("\n", $value->asString());
		} else {
			$storedCells = array();
		}
		$this->_childCells = array();
		for ($i = 1; $i <= $this->_numCells; $i++) {
			if (isset($storedCells[$i-1]))
				$this->_childCells[$i] = explode("\t", $storedCells[$i-1]);
			else
				$this->_childCells[$i] = array();
		}
		
// 		for ($i = $this->_numCells; $i < count($storedCells); $i++) {
// 			$this->_childCells[$this->_numCells] = array_merge(
// 											$this->_childCells[$this->_numCells],
// 											explode("\t", $storedCells[$i]));
// 		}
	}
	
	/**
	 * Update the order of our children
	 * 
	 * @param ref array $orderedChildren
	 * @return void
	 * @access private
	 * @since 1/23/06
	 */
	function _updateChildOrder ( &$orderedChildren ) {
		$childIds = array();
		foreach(array_keys($orderedChildren) as $key) {
			$child =& $orderedChildren[$key];
			$childId =& $child->getId();
			$childIds[] = $childId->getIdString();
		}
		$valueObj =& String::withValue(implode("\t", $childIds));
		
		// Get the nav info
		$idManager =& Services::getService("Id");
		$navRecords =& $this->_asset->getRecordsByRecordStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs'));
		$navRecord =& $navRecords->next();
		
		// Order part
		$partId =& $idManager->getId('Repository::edu.middlebury.segue.sites_repository'
			.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.child_order');
		$parts =& $navRecord->getPartsByPartStructure($partId);
		if ($parts->hasNext()) {
			$part =& $parts->next();
			$part->updateValue($valueObj);
		} else {
			$navRecord->createPart($partId, $valueObj);
		}
	}
	
}