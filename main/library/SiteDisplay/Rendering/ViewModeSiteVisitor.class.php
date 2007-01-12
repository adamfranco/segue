<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ViewModeSiteVisitor.class.php,v 1.20 2007/01/12 20:28:00 adamfranco Exp $
 */ 

require_once(HARMONI."GUIManager/Components/Header.class.php");
require_once(HARMONI."GUIManager/Components/Menu.class.php");
require_once(HARMONI."GUIManager/Components/SubMenu.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemHeading.class.php");
require_once(HARMONI."GUIManager/Components/MenuItemLink.class.php");
require_once(HARMONI."GUIManager/Components/Heading.class.php");
require_once(HARMONI."GUIManager/Components/Footer.class.php");
require_once(HARMONI."GUIManager/Container.class.php");

require_once(HARMONI."GUIManager/Layouts/XLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/YLayout.class.php");
require_once(HARMONI."GUIManager/Layouts/TableLayout.class.php");

/**
 * The ViewModeVisitor traverses the site hierarchy, rendering each component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ViewModeSiteVisitor.class.php,v 1.20 2007/01/12 20:28:00 adamfranco Exp $
 */
class ViewModeSiteVisitor {
		
	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 4/3/06
	 */
	function ViewModeSiteVisitor () {
		/*********************************************************
		 * cell placeholders: 
		 *		target_id => [empty] GUI container object.
		 *********************************************************/
		$this->_emptyCells = array();
		
		/*********************************************************
		 * Contents of targets which have not yet been traversed-to
		 * 		target_id => GUI component to place in target.
		 *********************************************************/
		$this->_missingTargets = array();
		
		$this->_menuNestingLevel = 0;
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object Component 
	 * @access public
	 * @since 4/3/06
	 */
	function &visitBlock ( &$block ) {
		$guiContainer =& new Container (	new YLayout, BLOCK, 1);
		
		$pluginManager =& Services::getService('PluginManager');
		
		if (true) {
			$guiContainer->add(
				new Heading(
					$pluginManager->getPluginTitleMarkup($block->getAsset(), true), 
					2),
				null, null, null, TOP);
			$guiContainer->add(
				new Block(
					$pluginManager->getPluginText($block->getAsset(), true),
					STANDARD_BLOCK), 
				null, null, null, TOP);
		} else {
// 			$guiContainer->add(new Heading($block->getTitleMarkup(), 2), null, null, null, TOP);
// 			$guiContainer->add(new Block($block->getContentMarkup(), STANDARD_BLOCK), null, null, null, TOP);
		}
		
		return $guiContainer;
	}
	
	/**
	 * Visit a block and return the resulting GUI component. (A menu item)
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object MenuItem 
	 * @access public
	 * @since 4/3/06
	 */
	function &visitBlockInMenu ( &$block ) {
		// Create and return the component
		$menuItem =& new MenuItem(
							"<span style='font-weight: bold; font-size: large;'>"
							.$block->getTitleMarkup()
							."</span><br/>"
							.$block->getContentMarkup(),
							1);
		return $menuItem;
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return ref array
	 * @access public
	 * @since 4/3/06
	 */
	function &visitNavBlock ( &$navBlock ) {
		$menuItems = array();
		
		// Create the menu item
		$menuItems[] =& new MenuItemLinkWithAdditionalHtml(
							$navBlock->getTitleMarkup(),
							$this->getUrlForComponent($navBlock->getId()),
							$navBlock->isActive(),
							1,
							null,
							null,
							$navBlock->getDescription(),
							'');
		
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
		if ($navBlock->isActive()) {
			$childOrganizer =& $navBlock->getOrganizer();
			$childGuiComponent =& $childOrganizer->acceptVisitor($this);
			
			if (isset($this->_emptyCells[$navBlock->getTargetId()])) {
				$this->_emptyCells[$navBlock->getTargetId()]->add($childGuiComponent, null, '100%', null, TOP);
				unset($this->_emptyCells[$navBlock->getTargetId()]);
			} else {
				$this->_missingTargets[$navBlock->getTargetId()] =& $childGuiComponent;
			}
			
			$nestedMenuOrganizer =& $navBlock->getNestedMenuOrganizer();
			if (!is_null($nestedMenuOrganizer)) {
				$this->_menuNestingLevel++;
				$menuItems[] =& $nestedMenuOrganizer->acceptVisitor($this);
			} else {
				$this->_menuNestingLevel = 0;
			}
		}
		
		// return the menu items
		return $menuItems;
	}
	
	/**
	 * Visit a SiteNavBlock and return the site GUI component that corresponds to
	 *	it.
	 * 
	 * @param object SiteNavBlockSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitSiteNavBlock ( &$siteNavBlock ) {
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
		$childOrganizer =& $siteNavBlock->getOrganizer();
		$childGuiComponent =& $childOrganizer->acceptVisitor($this);
		
		// Check completeness and render any nodes still waiting for targets
		foreach (array_keys($this->_missingTargets) as $targetId) {
			$this->_emptyCells[$targetId]->add($this->_missingTargets[$targetId], null, '100%', null, TOP);
			unset($this->_emptyCells[$targetId]);
			unset($this->_missingTargets[$targetId]);
		}
		
		// returning the entire site in GUI component object tree.
// 		printpre($this);
// 		print "<hr/>";
// 		printpre($siteNavBlock->_director->_activeNodes);
		return $childGuiComponent;
	}

	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitFixedOrganizer ( &$organizer ) {
		$guiContainer =& new Container (new TableLayout($organizer->getNumColumns()),
										BLANK,
										1);
		
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child =& $organizer->getSubcomponentForCell($i);
			if (is_object($child)) {
				$guiContainer->add($child->acceptVisitor($this), null, null, null, TOP );
			} else {
				// This should be changed to a new container type which
				// only has one cell and does not add any HTML when rendered.
				$placeholder =& new Container(new XLayout, BLANK, 1);
				
				$this->_emptyCells[$organizer->getId().'_cell:'.$i] =& $placeholder;
				$guiContainer->add($placeholder, null, '100%', null, TOP);
			}
		}
		
		return $guiContainer;
	}
	
	
	/**
	 * Visit a fixed organizer and return the GUI component [a container] 
	 * that corresponds to it. Traverse-to/add child components.
	 * 
	 * @param object FixedOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitNavOrganizer ( &$organizer ) {
		return $this->visitFixedOrganizer($organizer);
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitFlowOrganizer( &$organizer ) {
		$numCells = $organizer->getTotalNumberOfCells();
		
		if ($organizer->getNumRows() == 0)
			$cellsPerPage = $numCells;
		// If we are limiting to a number of rows, we are paginating.
		else
			$cellsPerPage = $organizer->getNumColumns() * $organizer->getNumRows();
		
		$childGuiComponents = array();
		for ($i = 0; $i < $numCells; $i++) {
			$child =& $organizer->getSubcomponentForCell($i);
			$childGuiComponents[] =& $child->acceptVisitor($this);
		}
		
		$resultPrinter =& new ArrayResultPrinter($childGuiComponents,
									$organizer->getNumColumns(), $cellsPerPage);
		$resultPrinter->setRenderDirection($organizer->getDirection());
		$resultPrinter->setNamespace('pages_'.$organizer->getId());
		$resultPrinter->addLinksStyleProperty(new MarginTopSP("10px"));
		
		return $resultPrinter->getLayout();
	}
	
	/**
	 * Visit a menu organizer and return the menu GUI component that corresponds
	 * to it.
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &visitMenuOrganizer ( &$organizer ) {	
		// Choose layout direction based on number of rows
		if ($this->_menuNestingLevel) {
			$layout =& new YLayout();
		} else if ($organizer->getDirection() == "Left-Right/Top-Bottom") {
			$layout =& new XLayout();
		} else if ($organizer->getDirection() == "Right-Left/Top-Bottom") {
			$layout =& new XLayout();
			$layout->setRenderDirection("Right-Left/Top-Bottom");
		} else {
			$layout =& new YLayout();
		}
		
		if ($this->_menuNestingLevel)
			$guiContainer =& new SubMenu ( $layout, $this->_menuNestingLevel);
		else
			$guiContainer =& new Menu ( $layout, 1);
		
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child =& $organizer->getSubcomponentForCell($i);
			$childGuiComponents =& $child->acceptVisitor($this, true);
			if (is_array($childGuiComponents)) {
				foreach (array_keys($childGuiComponents) as $key)
					$guiContainer->add($childGuiComponents[$key]);
			} else {
				$guiContainer->add($childGuiComponents);
			}
		}
		
		return $guiContainer;
	}
	
	/**
	 * Answer the Url for this component id.
	 *
	 * Note: this is clunky that this object has to know about harmoni and 
	 * what action to target. Maybe rewrite...
	 * 
	 * @param string $id
	 * @return string
	 * @access public
	 * @since 4/4/06
	 */
	function getUrlForComponent ( $id ) {
		$harmoni =& Harmoni::instance();
		return $harmoni->request->quickURL("site", "view", array("node" => $id));
	}
}

?>