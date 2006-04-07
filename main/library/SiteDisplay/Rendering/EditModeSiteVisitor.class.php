<?php
/**
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.3 2006/04/07 15:16:04 adamfranco Exp $
 */

require_once(HARMONI."GUIManager/StyleProperties/VerticalAlignSP.class.php");

/**
 * The edit-mode site visitor renders the site for editing, displaying controls.
 * 
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.3 2006/04/07 15:16:04 adamfranco Exp $
 */
class EditModeSiteVisitor
	extends ViewModeSiteVisitor
{

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
		
		// Any further empty cells in fixed organizers should get controls to
		// add to them.
		foreach (array_keys($this->_emptyCells) as $id) {
			$this->_emptyCells[$id]->add(new UnstyledBlock(_('Insert new...')), null, '100%', null, TOP);
			unset($this->_emptyCells[$id]);
		}
		
		// returning the entire site in GUI component object tree.
// 		printpre($this);
// 		print "<hr/>";
// 		printpre($siteNavBlock->_director->_activeNodes);
		return $childGuiComponent;
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
		
		$heading =& $guiContainer->add(new Heading($block->getTitleMarkup(), 2), null, null, null, TOP);
		$content =& $guiContainer->add(new Block($block->getContentMarkup(), STANDARD_BLOCK), null, null, null, TOP);
		
		$primaryColor = '#090';
		$secondaryColor = '#9F9';
		$halfLineWidth = 1;
		$lineWidth = ($halfLineWidth * 2).'px'; $halfLineWidth = $halfLineWidth.'px';
		$heading->setPreHTML("<div style='"
			."background-color: $secondaryColor; "
			."border: $lineWidth solid $primaryColor; "
			."'>"._('Block')."</div>"
			.$heading->getPreHTML($null = null));
			
		$styleCollection =& new StyleCollection(
									'.block_side_outline', 
									'block_side_outline', 
									'Side Outline', 
									'A side outline around block titles');
		$styleCollection->addSP(new BorderLeftSP($lineWidth, 'solid', $primaryColor));
		$styleCollection->addSP(new BorderRightSP($lineWidth, 'solid', $primaryColor));
		$heading->addStyle($styleCollection);
		
		$styleCollection =& new StyleCollection(
									'.block_bottom_outline', 
									'block_bottom_outline', 
									'Side Outline', 
									'A side outline around block content');
		$styleCollection->addSP(new BorderLeftSP($lineWidth, 'solid', $primaryColor));
		$styleCollection->addSP(new BorderRightSP($lineWidth, 'solid', $primaryColor));
		$styleCollection->addSP(new BorderBottomSP($lineWidth, 'solid', $primaryColor));
		$content->addStyle($styleCollection);
		
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
		$guiContainer =& parent::visitNavBlock($navBlock);
		
		$primaryColor = '#090';
		$secondaryColor = '#9F9';
		$halfLineWidth = 1;
		$lineWidth = ($halfLineWidth * 2).'px'; $halfLineWidth = $halfLineWidth.'px';
		$guiContainer->setPreHTML("<div style='"
			."background-color: $secondaryColor; "
			."border-top: $lineWidth solid $primaryColor; "
			."border-left: $lineWidth solid $primaryColor; "
			."border-right: $lineWidth solid $primaryColor; "
			."color: #000; "
// 			."margin-top: 3px; margin-left: 3px; margin-right: 3px; "
			."'>"._('NavBlock')."</div>"
			."<div style='"
			."border: $lineWidth solid $primaryColor; "
			."'>"
			.$guiContainer->getPreHTML($null = null));
		$guiContainer->setPostHTML($guiContainer->getPostHTML($null = null)."</div>");
		
		
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
	function &visitFixedOrganizer ( &$organizer ) {
		$guiContainer =& new Container (new TableLayout(
												$organizer->getNumColumns(), 
												'border: 1px solid #F00; padding: 6px;'),
										BLANK,
										1);
		
		// Ordered indicies are to be used in a left-right/top-bottom manner, but
		// may be returned in various orders to reflect another underlying fill direction.
		$orderedIndices = $organizer->getVisibleOrderedIndices();
		
		foreach ($orderedIndices as $i) {
			$child =& $organizer->getSubcomponentForCell($i);
			if (is_object($child)) {
				$guiContainer->add($child->acceptVisitor($this), null, null, null, TOP);
			} else {
				// This should be changed to a new container type which
				// only has one cell and does not add any HTML when rendered.
				$placeholder =& new Container(new XLayout, BLANK, 1);
				
				$this->_emptyCells[$organizer->getId().'_cell:'.$i] =& $placeholder;
				$guiContainer->add($placeholder, null, '100%', null, TOP);
			}
		}
		
		$primaryColor = '#F00';
		$secondaryColor = '#F99';
		$halfLineWidth = 1;
		$lineWidth = ($halfLineWidth * 2).'px'; $halfLineWidth = $halfLineWidth.'px';
		$guiContainer->setPreHTML("<div style='"
			."background-color: $secondaryColor; "
			."border-top: $lineWidth solid $primaryColor; "
			."border-left: $lineWidth solid $primaryColor; "
			."border-right: $lineWidth solid $primaryColor;"
			."'>"._("Fixed Organizer")."</div>"
			.$guiContainer->getPreHTML($null = null));
		
		$styleCollection =& new StyleCollection(
									'.org_red_outline', 
									'org_red_outline', 
									'Red Outline', 
									'A red outline around organizers');
		$styleCollection->addSP(new BorderSP($halfLineWidth, 'solid', $primaryColor));
		$styleCollection->addSP(new HeightSP('100%'));
		$guiContainer->addStyle($styleCollection);
		
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
		$guiContainer =& new Container (	new TableLayout($organizer->getNumColumns(), "border: 1px solid #00F; padding: 6px;"),
										BLANK,
										1);
		
		// Ordered indicies are to be used in a left-right/top-bottom manner, but
		// may be returned in various orders to reflect another underlying fill direction.
		$orderedIndices = $organizer->getVisibleOrderedIndices();
		
		foreach ($orderedIndices as $i) {
			$child =& $organizer->getSubcomponentForCell($i);
			$guiContainer->add($child->acceptVisitor($this), null, '100%', null, TOP);
		}
		
		
		$guiContainer->add(new UnstyledBlock(_('Append new...')), null, '100%', null, TOP);
		
		$primaryColor = '#00F';
		$secondaryColor = '#99F';
		$halfLineWidth = 1;
		$lineWidth = ($halfLineWidth * 2).'px'; $halfLineWidth = $halfLineWidth.'px';
		$guiContainer->setPreHTML("<div style='"
			."background-color: $secondaryColor; "
			."border-top: $lineWidth solid $primaryColor; "
			."border-left: $lineWidth solid $primaryColor; "
			."border-right: $lineWidth solid $primaryColor;"
			."'>"._("Flow Organizer")."</div>"
			.$guiContainer->getPreHTML($null = null));
		
		$styleCollection =& new StyleCollection(
									'.org_blue_outline', 
									'org_blue_outline', 
									'Blue Outline', 
									'A blue outline around organizers');
		$styleCollection->addSP(new BorderSP($halfLineWidth, 'solid', $primaryColor));
		$guiContainer->addStyle($styleCollection);
		
		return $guiContainer;
	}
	
	/**
	 * Visit a menu organizer and return the menu GUI component that corresponds
	 * to it.
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return object Component
	 * @access publicZ
	 * @since 4/3/06
	 */
	function &visitMenuOrganizer ( &$organizer ) {	
		$guiContainer =& parent::visitMenuOrganizer($organizer);
		$guiContainer->add(new MenuItem(_('Append new...'), 2), null, '100%', null, TOP);
		
// 		$primaryColor = '#90C';
// 		$secondaryColor = '#96C';
// 		$halfLineWidth = 1;
// 		$lineWidth = ($halfLineWidth * 2).'px'; $halfLineWidth = $halfLineWidth.'px';
// 		$guiContainer->setPreHTML("<div style='"
// 			."background-color: $secondaryColor; "
// 			."border-top: $lineWidth solid $primaryColor; "
// 			."border-left: $lineWidth solid $primaryColor; "
// 			."border-right: $lineWidth solid $primaryColor;"
// 			."margin-top: 3px; margin-left: 3px; margin-right: 3px; "
// 			."'>"._('Menu')."</div>"
// 			.$guiContainer->getPreHTML($null = null));
		
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
		return $harmoni->request->quickURL("site", "newEdit", array("node" => $id));
	}
}

?>