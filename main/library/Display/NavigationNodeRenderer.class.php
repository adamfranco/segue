<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavigationNodeRenderer.class.php,v 1.3 2006/01/20 20:53:25 adamfranco Exp $
 */ 

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
 * @version $Id: NavigationNodeRenderer.class.php,v 1.3 2006/01/20 20:53:25 adamfranco Exp $
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
		$component =& new MenuItemLink(
						$this->_asset->getDisplayName(), 
						$this->getMyUrl(), 
						$this->_active,
						$level);
						
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
				$childRenderer =& NodeRenderer::forAsset($children[$i]);
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
				$cellWidth = '200px';
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
								$cellWidth, $cellHeight, CENTER, TOP);
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
					$childRenderer =& NodeRenderer::forAsset($children[$i]);
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
		$orderedChildren = array();
		$unorderedChildren = array();
		$children =& $this->_asset->getAssets();
		while ($children->hasNext()) {
			$child =& $children->next();
			$childId =& $child->getId();
			
			if (false) {
				// @todo add order checking
			} else {
				$unorderedChildren[] =& $child;
			}
		}
		
		for ($i = 0; $i < count($unorderedChildren); $i++)
			$orderedChildren[] =& $unorderedChildren[$i];
		
		return $orderedChildren;
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
		$this->_targetOverride = $value->value();
	}
	
}