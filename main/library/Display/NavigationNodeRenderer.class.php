<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavigationNodeRenderer.class.php,v 1.14 2006/01/26 21:15:18 adamfranco Exp $
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
 * @version $Id: NavigationNodeRenderer.class.php,v 1.14 2006/01/26 21:15:18 adamfranco Exp $
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
	 * Add first children to the active nodes array
	 * 
	 * @param object Asset $asset
	 * @return boolean true if this node is a navigation node.
	 * @access public
	 * @since 1/19/06
	 */
	function traverseActiveDown () {
		$this->setActive();		
		
		// Traverse down just the first children
		$orderedChildren =& $this->getOrderedChildren();
		$childNavFound = false;
		foreach (array_keys($orderedChildren) as $key) {
			$childNavFound = $orderedChildren[$key]->traverseActiveDown();
			if ($childNavFound)
				break;
		}
		return true;
	}
	
	/**
	 * Answer the GUI component for the navegational item.
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderNavComponent ($level = 1) { 
		$links = array();
		$harmoni =& Harmoni::instance();
		$id =& $this->getId();
		$links[_('add child')] = $harmoni->request->quickURL('site', 'add', 
									array('parent_id' => $id->getIdString()));
		
		$component =& new MenuItemLinkWithAdditionalHtml(
						$this->_asset->getDisplayName(), 
						$this->getMyUrl(), 
						$this->isActive(),
						$level,
						null,
						null,
						null,
						$this->getSettingsForm($links));
						
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
		// In single-cell arrangement, each child will be given its own
		// target with which it can subdivide if necessary for any of its
		// children.
		if ($this->getNumCells() <= 1)
			return $this->renderSingleCellTarget();
		
		// In multi-cell arrangements the child navigational components will
		// be rendered in all cells but the one designated as the 'target'.
		// The 'active' child is given the 'target' cell in which to render
		// its children.
		else
			return $this->renderMultiCellTarget($level);
	}
	
	/**
	 * Render the single-cell target for this node
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Container
	 * @access public
	 * @since 1/25/06
	 */
	function &renderSingleCellTarget ($level = 1) {
		$container =& new Container(new YLayout, BLANK, 1);
		
		$children =& $this->getOrderedChildren();
		for ($i = 0; $i < count($children); $i++) {
			$childRenderer =& $children[$i];
			
			// print a heading if availible
			if ($childRenderer->getTitle()) {
				$container->add(
						new Heading($childRenderer->getTitle(), 2),
						null, null, LEFT, TOP);
			}
			
			// print the content
			$container->add(
					$childRenderer->renderTargetComponent(),
					null, null, LEFT, TOP);
		}
		
		if (!count($children)) {
			$container->add(
					new Block("[debug: no children]<br/>[Cell[$i]=target]", 
						EMPHASIZED_BLOCK),
					null, null, CENTER, TOP);
		}
		return $container;
	}
	
	/**
	 * Render Multi-celled target
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Container
	 * @access public
	 * @since 1/25/06
	 */
	function &renderMultiCellTarget ($level = 1) {
		$numCells = $this->getNumCells();
		$targetOverride = $this->getTargetOverride();
		if (!$targetOverride || $targetOverride > $numCells)
			throwError(new Error("$targetOverride overflows number of cells, $numCells.", __FILE__, TRUE));		
		$xLayout =& new XLayout();
		$yLayout =& new YLayout();
		$children =& $this->getOrderedChildren();
	
		// Make our container
		if ($this->getLayoutArrangement() == 'columns') {
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
							NULL, $cellHeight, LEFT, TOP);
			} else {
				$cells[$cellIndex] =& $container->add(
							new Menu($cellLayout, $level), 
							$cellWidth, $cellHeight, LEFT, TOP);
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
			$cellsWithContent = array();
			for ($i = 0; $i < count($children); $i++) {
				$childRenderer =& $children[$i];
				$childCell = $this->getDestinationCell($childRenderer->getId());
				$cellsWithContent[] = $childCell;
				$cells[$childCell]->add(
					$childRenderer->renderNavComponent(),
					null, null, LEFT, TOP);
				
				if ($childRenderer->isActive()) {
					$targetCell->add(
						$childRenderer->renderTargetComponent(),
						null, null, LEFT, TOP);
				}
			}
			$cellsWithContent = array_unique($cellsWithContent);
			for ($i = 1; $i < $numCells; $i++) {
				if (!in_array($i, $cellsWithContent)) {
					$cells[$i]->add(
						new MenuItem("[debug: no children]<br/>[Cell[$i]]", 
							$level),
						null, null, CENTER, TOP);
				}
			}
		}
		
		return $container;
	}
	
	/**
	 * Answer an ordered array of children
	 * 
	 * @return array of NodeRenderer objects
	 * @access public
	 * @since 1/20/06
	 */
	function &getOrderedChildren () {
		if (!isset($this->_orderedChildren)) {
			if (!isset($this->_childOrder))
				$this->_loadNavRecord();
			$orderedChildren = array();
			$orderedChildIds = array();
			$unorderedChildren = array();
			$unorderedChildIds = array();
			$children =& $this->_asset->getAssets();
			while ($children->hasNext()) {
				$child =& $this->getRendererForChildAsset($children->next());
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
			$this->getOrderedChildren();
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
		if (!isset($this->_childCells)) 
			$this->_loadNavRecord();
			
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
		if (!$navRecords->hasNext())
			throwError(new Error("Manditory Navegation data missing.", __FILE__, TRUE));		
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