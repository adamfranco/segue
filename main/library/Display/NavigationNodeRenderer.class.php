<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavigationNodeRenderer.class.php,v 1.2 2006/01/19 21:31:50 adamfranco Exp $
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
 * @version $Id: NavigationNodeRenderer.class.php,v 1.2 2006/01/19 21:31:50 adamfranco Exp $
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
		
		// Make our container
		$xLayout =& new XLayout();
		$yLayout =& new YLayout();
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
		for ($i = 1; $i <= $numCells; $i++) {
			$cell =& $container->add(
							new Container($cellLayout, BLANK, 1), 
							$cellWidth, $cellHeight, CENTER, TOP);
			
			if ($i == $targetOverride)
				$targetCell =& $cell;
			else
				$cells[$i] =& $cell;
		}
		
		
		// Add our children to our cells
		$children =& $this->_asset->getAssets();
		if (!$children->hasNext()) {
			for ($i = 1; $i < $numCells; $i++) {
				$cells[$i]->add(
					new Block("[debug: no children]<br/>[Cell[$i]]", 
						EMPHASIZED_BLOCK),
					null, null, CENTER, TOP);
			}
			
			$targetCell->add(
				new Block("[debug: no children]<br/>[Cell[$i]=target]", 
					EMPHASIZED_BLOCK),
				null, null, CENTER, TOP);
		} else {
			while ($children->hasNext()) {
				$childRenderer =& NodeRenderer::forAsset($children->next());
				$childCell = $childRenderer->getDestination();
				if (!isset($cells[$childCell]))
					$childCell = 1;
				
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
		
		return $container;
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