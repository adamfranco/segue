<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ViewModeSiteVisitor.class.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
 */ 

require_once(HARMONI."GUIManager/Components/Header.class.php");
require_once(HARMONI."GUIManager/Components/Menu.class.php");
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
 * @version $Id: ViewModeSiteVisitor.class.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
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
		$guiContainer =& new Container (	new YLayout, BLANK, 1);
		
		$guiContainer->add(new Heading($block->getTitleMarkup(), 2));
		$guiContainer->add(new Block($block->getContentMarkup(), STANDARD_BLOCK));
		
		return $guiContainer;
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object NavBlockSiteComponent $navBlock
	 * @return object Component 
	 * @access public
	 * @since 4/3/06
	 */
	function &visitNavBlock ( &$navBlock ) {
		// Traverse our child organizer, and place it in the _missingTargets array
		// if our target is not available.
		if ($navBlock->isActive()) {
			$childOrganizer =& $navBlock->getOrganizer();
			$childGuiComponent =& $childOrganizer->acceptVisitor($this);
				
			if (isset($this->_emptyCells[$navBlock->getTargetId()])) {
				$this->_emptyCells[$navBlock->getTargetId()]->add($childGuiComponent);
				unset($this->_emptyCells[$navBlock->getTargetId()]);
			} else {
				$this->_missingTargets[$navBlock->getTargetId()] =& $childGuiComponent;
			}
		}
		
		// Create and return the component
		$menuItem =& new MenuItemLinkWithAdditionalHtml(
							$navBlock->getTitleMarkup(),
							$this->getUrlForComponent($navBlock->getId()),
							$navBlock->isActive(),
							1,
							null,
							null,
							$navBlock->getDescription(),
							'');
		return $menuItem;
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
			$this->_emptyCells[$targetId]->add($this->_missingTargets[$targetId]);
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
		
		// Ordered indicies are to be used in a left-right/top-bottom manner, but
		// may be returned in various orders to reflect another underlying fill direction.
		$orderedIndices = $organizer->getVisibleOrderedIndices();
		
		foreach ($orderedIndices as $i) {
			$child =& $organizer->getSubcomponentForCell($i);
			if (is_object($child)) {
				$guiContainer->add($child->acceptVisitor($this));
			} else {
				// This should be changed to a new container type which
				// only has one cell and does not add any HTML when rendered.
				$placeholder =& new Container(new XLayout, BLANK, 1);
				
				$this->_emptyCells[$organizer->getId().'_cell:'.$i] =& $placeholder;
				$guiContainer->add($placeholder);
			}
		}
		
		return $guiContainer;
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
		$guiContainer =& new Container (	new TableLayout($organizer->getNumColumns()),
										BLANK,
										1);
		
		// Ordered indicies are to be used in a left-right/top-bottom manner, but
		// may be returned in various orders to reflect another underlying fill direction.
		$orderedIndices = $organizer->getVisibleOrderedIndices();
		
		foreach ($orderedIndices as $i) {
			$child =& $organizer->getSubcomponentForCell($i);
			$guiContainer->add($child->acceptVisitor($this));
		}
		
		return $guiContainer;
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
		if ($organizer->getNumRows() == 1)
			$layout =& new XLayout();
		else 
			$layout =& new YLayout();
		
		$guiContainer =& new Menu ( $layout, 1);
		
		// Ordered indicies are to be used in a left-right/top-bottom manner, but
		// may be returned in various orders to reflect another underlying fill direction.
		$orderedIndices = $organizer->getVisibleOrderedIndices();
		
		foreach ($orderedIndices as $i) {
			$child =& $organizer->getSubcomponentForCell($i);
			$guiContainer->add($child->acceptVisitor($this));
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
		return $harmoni->request->quickURL("site", "newView", array("node" => $id));
	}
}

?>