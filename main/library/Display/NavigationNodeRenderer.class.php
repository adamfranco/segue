<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: NavigationNodeRenderer.class.php,v 1.25 2006/02/20 17:58:18 adamfranco Exp $
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
 * @version $Id: NavigationNodeRenderer.class.php,v 1.25 2006/02/20 17:58:18 adamfranco Exp $
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
	 * Answer the title that should be displayed in a heading for this node.
	 * 
	 * @return string
	 * @access public
	 * @since 1/19/06
	 */
	function getHeading () {
		return $this->getTitle().$this->getSettingsForm();
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
		
		$component =& new MenuItemLinkWithAdditionalHtml(
						$this->_asset->getDisplayName(), 
						$this->getMyUrl(), 
						$this->isActive(),
						$level,
						null,
						null,
						null,
						$this->getSettingsForm($links));
		$component->setId($id->getIdString()."-nav");
		
		if ($this->getLayoutArrangement() != 'nested' || !$this->isActive()) {
			return $component;
		} else {
			$component->setId($id->getIdString()."-cell-1");
			$allComponents = array();
			$allComponents[] =& $component;
			
			$children =& $this->getOrderedChildren();
			for ($i = 0; $i < count($children); $i++) {
				$childRenderer =& $children[$i];
				if($this->getDestinationCell($childRenderer->getId()) == 1)
					$allComponents[] =& $childRenderer->renderNavComponent($level + 1);
			}
			
			return $allComponents;
		}
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
			$target =& $this->renderSingleCellTarget();
		
		// In multi-cell arrangements the child navigational components will
		// be rendered in all cells but the one designated as the 'target'.
		// The 'active' child is given the 'target' cell in which to render
		// its children.
		else
			$target =& $this->renderMultiCellTarget($level);
		
		$id =& $this->getId();
		$target->setId($id->getIdString()."-target");
		return $target;
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
			if ($childRenderer->getHeading()) {
				$title =& $container->add(
							new Heading($childRenderer->getHeading(), 2),
							null, null, LEFT, TOP);
				$childId =& $childRenderer->getId();
				$title->setId($childId->getIdString()."-title");
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
		$layoutArrangement = $this->getLayoutArrangement();
		if (!$targetOverride || $targetOverride > $numCells)
			throwError(new Error("$targetOverride overflows number of cells, $numCells.", __FILE__, TRUE));		
		$xLayout =& new XLayout();
		$yLayout =& new YLayout();
		$children =& $this->getOrderedChildren();
	
		// Make our container
		if ($layoutArrangement == 'rows') {
			$layout =& $yLayout;
			$cellLayout =& $xLayout;
			$cellWidth = NULL;
			$cellHeight = NULL;
		} else {
			$layout =& $xLayout;
			$cellLayout =& $yLayout;
			$cellWidth = '250px';
			$cellHeight = NULL;
		}
		$container =& new Container($layout, BLANK, 1);
		
		
		// Add our cells
		$cells = array();
		$overallCellNumber = 1;
		$cellIndex = 1;
		$id =& $this->getId();
		$idString = $id->getIdString();
		while ($overallCellNumber <= $numCells) {
			if ($overallCellNumber == $targetOverride) {
				$targetCell =& $container->add(
							new Container($yLayout, BLANK, 1), 
							NULL, $cellHeight, LEFT, TOP);
			} else if ($layoutArrangement == 'nested' && $overallCellNumber == 1) {
				$cellIndex++;
			} else {
				$cells[$cellIndex] =& $container->add(
							new Menu($cellLayout, $level), 
							$cellWidth, $cellHeight, LEFT, TOP);
				$cells[$cellIndex]->setId($idString."-cell-".$cellIndex);
				$cellIndex++;
			}
			$overallCellNumber++;
		}		
		
		// Add our children to our cells
		if (!count($children)) {
			for ($i = 1; $i < $numCells; $i++) {
				if (isset($cells[$i]))
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
				
				if (!($layoutArrangement == 'nested' && $childCell == 1)) {
					$this->addChildNavToCell(
						$childRenderer->renderNavComponent(), 
						$cells[$childCell]);
				}
				
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
	 * Add child Nav components to a cell
	 * 
	 * @param mixed $itemOrArray array OR object Component
	 * @param object Component $cell
	 * @return void
	 * @access public
	 * @since 1/27/06
	 */
	function addChildNavToCell ( &$itemOrArray, &$cell ) {
		ArgumentValidator::validate($cell, ExtendsValidatorRule::getRule("Container"));
		// If the child is nested add all rendered elements
			if (is_array($itemOrArray)) {
				for ($i = 0; $i < count($itemOrArray); $i++)
				$this->addChildNavToCell($itemOrArray[$i], $cell);
			}
			// Otherwise just add the component
			else {
				$cell->add(
					$itemOrArray,
					null, null, LEFT, TOP);
			}
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
			
			$this->_orderedChildren = array();
			$orderedChildren = array();
			$unorderedChildren = array();
			
			$children =& $this->_asset->getAssets();
			while ($children->hasNext()) {
				$child =& $this->getRendererForChildAsset($children->next());
				$childId =& $child->getId();
				
				$position = $this->_childOrder->getPosition($childId);
				if ($position !== false) {
					$orderedChildren[$position] =& $child;
				} else {
					$unorderedChildren[] =& $child;
				}
			}
			ksort($orderedChildren);
			for ($i = 0; $i < count($unorderedChildren); $i++) {
				$orderedChildren[] =& $unorderedChildren[$i];
				$this->_childOrder->addItem($unorderedChildren[$i]->getId());
			}
			
			// ensure that children are keyed from zero straight up.
			$save = false;
			$i = 0;
			$newChildOrder =& new OrderedSet($this->getId());
			foreach (array_keys($orderedChildren) as $key) {
				$this->_orderedChildren[$i] =& $orderedChildren[$key];
				$newChildOrder->addItem($this->_orderedChildren[$i]->getId());
				if ($i != $key)
					$save = true;
				$i++;
			}
			
			if (count($unorderedChildren) || $save) {
				$this->_childOrder =& $newChildOrder;
				$this->saveChildOrder();
			}
				
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
	function &getChildOrder () {
		if (!isset($this->_orderedChildren))
			$this->getOrderedChildren();
		return $this->_childOrder;
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
			if (array_search($idString, $cellList) !== false) {
				if ($cell < $this->getNumCells())	
					return $cell;
				else
					return 1;
			}
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
	 * Print the form for adding child nodes
	 * 
	 * @return void
	 * @access public
	 * @since 1/31/06
	 */
	function printOptionAddChildForm () {
		$harmoni = Harmoni::instance();
		$id =& $this->getId();
		$idString = $id->getIdString();
		
		// Check Authorization
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"), $id))
		{
			$enabled = true;
		} else
			$enabled = false;
		
		
		$singNavUrl = $harmoni->request->quickURL('site', 'addnav', 
								array('parent_id' => $idString,
									'return_node' => RequestContext::value('node'),
									'nav_type' => 'single'));
		$navUrl = $harmoni->request->quickURL('site', 'addnav', 
								array('parent_id' => $idString,
									'return_node' => RequestContext::value('node')));
		$pluginUrl = $harmoni->request->quickURL('site', 'addplugin', 
								array('parent_id' => $idString,
									'type' => '______',
									'return_node' => RequestContext::value('node')));
		
		print "\n\t\t\t<div>";
// 		print "\n\t\t\t\t"._("New child node: ");
		print "\n\t\t\t\t<select";
		if ($enabled) {
			print " onchange='";
			print "if (this.value) {";
			print 	"if (this.value == \"single_nav\") {";
			print 		"goToValueInserted(\"".$singNavUrl."\", \"\");";
			print 	"}else if (this.value == \"nav\") {";
			print 		"goToValueInserted(\"".$navUrl."\", \"\");";
			print 	"} else {";
			print		"goToValueInserted(\"".$pluginUrl."\", this.value);";
			print 	"}";
			print "}";
			print "'";
		} else {
			print " disabled='disabled'";
		}
		print ">";
		print "\n\t\t\t\t\t<option>"._("Add a new child element...")."</option>";
		print "\n\t\t\t\t\t<option>------------</option>";
		print "\n\t\t\t\t\t<option value='single_nav'>"._("Container")."</option>";
		print "\n\t\t\t\t\t<option value='nav'>"._("Navigational Container")."</option>";
		print "\n\t\t\t\t\t<option>------------</option>";
		
		// Loop through all plugins. don't print Authority for any, don't print
		// domain for SeguePlugins. 
		print "\n\t\t\t\t\t<option value='SeguePlugins::Segue::TextBlock'>"._("Text Block")."</option>";
		print "\n\t\t\t\t\t<option value='SeguePlugins::Segue::Assignment'>"._("Assignment")."</option>";
		print "\n\t\t\t\t</select>";
		print "\n\t\t\t</div>";
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
		$part =& $this->getLayoutArrangementPart();
		$value =& $part->getValue();
		$this->_layoutArrangement = $value->asString();
		
		$part =& $this->getNumCellsPart();
		$value =& $part->getValue();
		$this->_numCells = $value->value();
		
		$part =& $this->getTargetOverridePart();
		$value =& $part->getValue();
		$this->_targetOverride = $value->value();
		
		$navRecord =& $this->getNavRecord();
		
		$sets =& Services::getService("Sets");
		$this->_childOrder =& new OrderedSet($this->getId());
		$parts =& $navRecord->getPartsByPartStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.child_order'));
		if ($parts->hasNext()) {
			$part =& $parts->next();
			$value =& $part->getValue();
			$this->_childOrder->initializeWithData($value->asString());
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
		for ($i = 1; $i < $this->_numCells; $i++) {
			if (isset($storedCells[$i-1]))
				$this->_childCells[$i] = array_unique(explode("\t", $storedCells[$i-1]));
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
	 * Answer the nav Record for this asset
	 * 
	 * @return object Record
	 * @access public
	 * @since 2/17/06
	 */
	function &getNavRecord () {
		$idManager =& Services::getService("Id");
		$navRecords =& $this->_asset->getRecordsByRecordStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs'));
		if (!$navRecords->hasNext())
			throwError(new Error("Manditory Navegation data missing.", __FILE__, TRUE));		
		return $navRecords->next();
	}
	
	/**
	 * Answer the num_cells nav part
	 * 
	 * @return object Part
	 * @access public
	 * @since 2/17/06
	 */
	function &getNumCellsPart () {
		$idManager =& Services::getService("Id");
		$navRecord =& $this->getNavRecord();
		$parts =& $navRecord->getPartsByPartStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.num_cells'));
		return $parts->next();
	}
	
	/**
	 * Answer the target override nav part
	 * 
	 * @return object Part
	 * @access public
	 * @since 2/17/06
	 */
	function &getTargetOverridePart () {
		$idManager =& Services::getService("Id");
		$navRecord =& $this->getNavRecord();
		$parts =& $navRecord->getPartsByPartStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.target_override'));
		return $parts->next();
	}
	
	/**
	 * Answer the layout arrangement nav part
	 * 
	 * @return object Part
	 * @access public
	 * @since 2/17/06
	 */
	function &getLayoutArrangementPart () {
		$idManager =& Services::getService("Id");
		$navRecord =& $this->getNavRecord();
		$parts =& $navRecord->getPartsByPartStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.layout_arrangement'));
		return $parts->next();
	}
	
	/**
	 * Update the order of our children
	 * 
	 * @param ref array $orderedChildren
	 * @return void
	 * @access public
	 * @since 1/23/06
	 */
	function saveChildOrder () {
		$valueObj =& String::withValue($this->_childOrder->toDataString());
		
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
	
	/**
	 * Update the destination cell of a child
	 * 
	 * @param object Id $id
	 * @param integer $cell
	 * @return void
	 * @access public
	 * @since 1/30/06
	 */
	function updateChildCell ( &$id, $cell ) {
		if (!isset($this->_childCells)) {
			$this->_loadNavRecord();
		}
		
		if ($cell < 1 || $cell > $this->_numCells)
			return;
		
		$idString = $id->getIdString();
		
		for ($i = 1; $i < $this->_numCells; $i++) {
			// Add the child to the new cell.
			if ($i == $cell) {
				$this->_childCells[$i][] = $idString;
			}
			// remove the child from is previous cell.
			else {
				$key = array_search($idString, $this->_childCells[$i]);
				if ($key !== false)
					unset($this->_childCells[$i][$key]);
			}
		}
		
		// implode our data to save it.
		$data = array();
		for ($i = 1; $i < $this->_numCells; $i++) {
			$data[] = implode("\t", $this->_childCells[$i]);
		}
		$data = implode("\n", $data);
		
		
		// Save our data
		$valueObj =& String::withValue($data);
		
		// Get the nav info
		$idManager =& Services::getService("Id");
		$navRecords =& $this->_asset->getRecordsByRecordStructure(
			$idManager->getId(
				'Repository::edu.middlebury.segue.sites_repository'
				.'::edu.middlebury.segue.nav_nod_rs'));
		$navRecord =& $navRecords->next();
		
		// Order part
		$partId =& $idManager->getId('Repository::edu.middlebury.segue.sites_repository'
			.'::edu.middlebury.segue.nav_nod_rs.edu.middlebury.segue.nav_nod_rs.child_cells');
		$parts =& $navRecord->getPartsByPartStructure($partId);
		if ($parts->hasNext()) {
			$part =& $parts->next();
			$part->updateValue($valueObj);
		} else {
			$navRecord->createPart($partId, $valueObj);
		}
	}
	
	/**
	 * Answer the delete confirm message
	 * 
	 * @return string
	 * @access public
	 * @since 2/2/06
	 */
	function getDeleteConfirmMessage () {
		return _('Are you sure that you wish to delete this node\nand its children?');
	}
	
	/**
	 * Answer the elements to flash on delete.
	 * 
	 * @return string
	 * @access public
	 * @since 2/2/06
	 */
	function getElementsToFlashOnDelete () {
		$id =& $this->getId();
		$idString = $id->getIdString();
		$ids = array('"'.$idString.'-nav"', '"'.$idString.'-target"', '"'.$idString.'-target"');
		return 'new Array('.implode(", ", $ids).')';
	}
}