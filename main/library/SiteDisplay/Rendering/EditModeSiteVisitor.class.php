<?php
/**
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.13 2006/04/12 21:07:15 adamfranco Exp $
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
 * @version $Id: EditModeSiteVisitor.class.php,v 1.13 2006/04/12 21:07:15 adamfranco Exp $
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
		// enter links in our head to load needed javascript libraries
		$harmoni =& Harmoni::instance();
		$outputHandler =& $harmoni->getOutputHandler();
		$outputHandler->setHead(
			$outputHandler->getHead()
			."
		<script src='".MYPATH."/main/library/SiteDisplay/scriptaculous-js/lib/prototype.js' type='text/javascript'></script>
		<script src='".MYPATH."/main/library/SiteDisplay/scriptaculous-js/src/scriptaculous.js' type='text/javascript'></script>

		<style type='text/css'>
			.drop_hover {
				border: 4px inset #F00;
			}
		</style>
");
		
		// Print out Javascript functions needed by our methods
		$this->printJavascript();
		
		
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
		
		ob_start();
		print "\n\t\t\t\tControls:";
		printpre("Id: ".$block->getId()."\nAccepts:");
// 		printpre($droppableIds);
		$controlsHTML = $this->getControlsHTML(_("<em>Block</em>"), ob_get_clean(), '#090', '#9F9', '#6C6');
		$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
					
		$styleCollection =& new StyleCollection(
									'.block_side_outline', 
									'block_side_outline', 
									'Side Outline', 
									'A side outline around block titles');
		$styleCollection->addSP(new BorderTopSP($lineWidth, 'solid', $primaryColor));
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
		
		if (count($block->getVisibleDestinationsForPossibleAddition()))
			$this->wrapAsDraggable($guiContainer, $block->getId(), 'Block');
		
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
		
		ob_start();
		print "\n\t\t\t\tControls:";
		printpre("Id: ".$navBlock->getId()."\nAccepts:");
// 		printpre($droppableIds);
		$controlsHTML = $this->getControlsHTML($navBlock->getDisplayName()._(" <em>Link</em>"), ob_get_clean(), '#090', '#9F9', '#6C6');
		$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
		
		$styleCollection =& new StyleCollection(
									'.nav_outline', 
									'nav_outline', 
									'Side Outline', 
									'A side outline around block titles');
		$styleCollection->addSP(new BorderSP('2px', 'solid', '#090'));
		$guiContainer->addStyle($styleCollection);
		
		if (count($navBlock->getVisibleDestinationsForPossibleAddition()))
			$this->wrapAsDraggable($guiContainer, $navBlock->getId(), 'NavBlock');
		
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
				$childComponent =& $guiContainer->add($child->acceptVisitor($this), 
														null, null, null, TOP);
			} else {
				// This should be changed to a new container type which
				// only has one cell and does not add any HTML when rendered.
				$childComponent =& new Container(new XLayout, BLANK, 1);
				
				$this->_emptyCells[$organizer->getId().'_cell:'.$i] =& $childComponent;
				$guiContainer->add($childComponent, null, '100%', null, TOP);
			}
			
			$this->wrapAsDroppable($childComponent, 
				$organizer->getId()."_cell:".$i,
				array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
		}
		
		ob_start();
		print "\n\t\t\t\tControls:";
		printpre("Id: ".$organizer->getId()."\nAccepts:");
		printpre($droppableIds);
		$controlsHTML = $this->getControlsHTML($organizer->getDisplayName(), ob_get_clean(), '#F00', '#F99', '#F66');
		$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
		
		$styleCollection =& new StyleCollection(
									'.org_red_outline', 
									'org_red_outline', 
									'Red Outline', 
									'A red outline around organizers');
		$styleCollection->addSP(new BorderSP('1px', 'solid', '#F00'));
		$styleCollection->addSP(new HeightSP('100%'));
		$guiContainer->addStyle($styleCollection);
		
		if (count($organizer->getVisibleDestinationsForPossibleAddition()))
			$this->wrapAsDraggable($guiContainer, $organizer->getId(), 'FixedOrganizer');
		
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
		$guiContainer =& new Container (	new TableLayout($organizer->getNumColumns(), "border: 1px solid #00F; padding: 6px;"),
										BLANK,
										1);
		
		// Ordered indicies are to be used in a left-right/top-bottom manner, but
		// may be returned in various orders to reflect another underlying fill direction.
		$orderedIndices = $organizer->getVisibleOrderedIndices();
		
		foreach ($orderedIndices as $i) {
			$child =& $organizer->getSubcomponentForCell($i);
			$childComponent =& $guiContainer->add($child->acceptVisitor($this), null, '100%', null, TOP);
			
			$this->wrapAsDroppable($childComponent, 
				$organizer->getId()."_cell:".$i,
				array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
		}
		
		$i++;
		$childComponent =& $guiContainer->add(new UnstyledBlock(_('Append new...')), null, '100%', null, TOP);
		$this->wrapAsDroppable($childComponent, 
				$organizer->getId()."_cell:".$i,
				array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
		
		
		ob_start();
		print "\n\t\t\t\tControls:";
		printpre("Id: ".$organizer->getId()."\nAccepts:");
// 		printpre($droppableIds);
		$controlsHTML = $this->getControlsHTML($organizer->getDisplayName(), ob_get_clean(), '#00F', '#99F', '#66F');
		$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
				
		$styleCollection =& new StyleCollection(
									'.org_blue_outline', 
									'org_blue_outline', 
									'Blue Outline', 
									'A blue outline around organizers');
		$styleCollection->addSP(new BorderSP('1px', 'solid', '#00F'));
		$guiContainer->addStyle($styleCollection);
		
		if (count($organizer->getVisibleDestinationsForPossibleAddition()))
			$this->wrapAsDraggable($guiContainer, $organizer->getId(), 'FlowOrganizer');
		
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
			$childComponent =& $guiContainer->add($child->acceptVisitor($this));
			
			$this->wrapAsDroppable($childComponent, 
				$organizer->getId()."_cell:".$i,
				array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
		}
		
		$i++;
		$childComponent =& $guiContainer->add(new MenuItem(_('Append new...'), 2), null, '100%', null, TOP);
		$this->wrapAsDroppable($childComponent, 
				$organizer->getId()."_cell:".$i,
				array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
		
		ob_start();
		print "\n\t\t\t\tControls:";
		printpre("Id: ".$organizer->getId()."\nAccepts:");
// 		printpre($droppableIds);
		$controlsHTML = $this->getControlsHTML($organizer->getDisplayName(), ob_get_clean(), '#00F', '#99F', '#66F');
		$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
		
		$styleCollection =& new StyleCollection(
									'.menu_blue_outline', 
									'menu_blue_outline', 
									'Blue Outline', 
									'A blue outline around organizers');
		$styleCollection->addSP(new BorderSP('2px', 'solid', '#00F'));
		$guiContainer->addStyle($styleCollection);
		
		if (count($organizer->getVisibleDestinationsForPossibleAddition()))
			$this->wrapAsDraggable($guiContainer, $organizer->getId(), 'FlowOrganizer');
		
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
	
	/**
	 * Wrap the given component as a draggable element
	 * 
	 * @param object Component $component
	 * @param string $id
	 * @return void
	 * @access public
	 * @since 4/7/06
	 */
	function wrapAsDraggable (&$component, $id, $class) {
		$component->setPreHTML("<div id='$id' class='$class'>".$component->getPreHTML($null = null));	
		$component->setPostHTML(
			$component->getPostHTML($null = null)
			."
</div>

<script type='text/javascript'>
/* <![CDATA[ */
	
	new Draggable('$id',{revert:true});

/* ]]> */
</script>
");
	}
	
	/**
	 * Wrap the given component as a droppable element (that can have draggables dropped on it.
	 * 
	 * @param object Component $component
	 * @param string $id
	 * @return void
	 * @access public
	 * @since 4/7/06
	 */
	function wrapAsDroppable (&$component, $id, $allowedDraggables) {
		if (!count($allowedDraggables))
			return;
		
		$draggablesArray = "new Array('".implode("', '", $allowedDraggables)."')";

		$component->setPreHTML("<div id='$id'>".$component->getPreHTML($null = null));
		$dropConfirm = _('Are you sure that you want to move this element here?');
		
		$harmoni =& Harmoni::instance();
		$url = str_replace("XXXdraggableXXX", "' + draggableElement.id + '",
					str_replace("XXXdroppableXXX", "' + droppableElement.id + '",
						str_replace('&amp;', '&', 
							$harmoni->request->quickUrl('site', 'moveComponent', 
								array('component' => "XXXdraggableXXX", 
									'destination' => "XXXdroppableXXX")))));
		
		$component->setPostHTML(
			$component->getPostHTML($null = null)
			."
</div>

<script type='text/javascript'>
/* <![CDATA[ */
	
	Droppables.add('$id', {
			accept: ".$draggablesArray.",
			hoverclass: 'drop_hover',
			onDrop: function (draggableElement, droppableElement) {
				if (confirm ('$dropConfirm' 
					+ \"\\nElement, \" + draggableElement.id + ' was dropped on ' 
					+ droppableElement.id))
				{
					var moveUrl = '".$url."';
					window.location = moveUrl;
				} else {
					window.location.reload();
				}
			}
		});

/* ]]> */
</script>
");
	}
	
	/**
	 * Print javascript requirered by other methods.
	 * 
	 * @return void
	 * @access public
	 * @since 4/7/06
	 */
	function printJavascript () {
		$showControls = _("Show Controls");
		$hideControls = _("Hide Controls");
		print <<<END

<script type='text/javascript'>
/* <![CDATA[ */
		
	function showControlsLink(mainElement) {
		var controlsLink = getDescendentByClassName(mainElement, 'controls_link');
		controlsLink.style.visibility = 'visible';
	}
	
	function hideControlsLink(mainElement) {
		var controls = getDescendentByClassName(mainElement, 'controls');
		if (controls.style.display != 'block') {
			var controlsLink = getDescendentByClassName(mainElement, 'controls_link');
			controlsLink.style.visibility = 'hidden';
		}		
	}
	
	function toggleControls(mainElement) {
		var controls = getDescendentByClassName(mainElement, 'controls');
		
		// if controls aren't show, show them
		if (controls.style.display != 'block') {
			controls.style.display = 'block';
			
			var controlsLink = getDescendentByClassName(mainElement, 'controls_link');
			controlsLink.style.visibility = 'visible';
			controlsLink.innerHTML = '$hideControls';
		}
		// if they are shown, hide them.
		else {
			var controls = getDescendentByClassName(mainElement, 'controls');
			controls.style.display = 'none';
			
			var controlsLink = getDescendentByClassName(mainElement, 'controls_link');
			controlsLink.innerHTML = '$showControls';
		}
	}
	
	function getDescendentByClassName(element, className) {
		// base case, we found the element
		if (element.className == className)
			return element;
		
		// Check our children
		var child = element.firstChild;
		while (child) {
			var foundInChild = getDescendentByClassName(child, className);
			if (foundInChild)
				return foundInChild;
			child = child.nextSibling;
		}
		
		// if not found, return
		return false;
	}
	
	function doDrop(draggableElement, droppableElement) {
		alert ("Element, " + draggableElement.id + " was dropped on " + droppableElement.id);
	}
	
/* ]]> */
</script>

END;
	}
	
	/**
	 * Answer the HTML for the controls top-bar
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 4/7/06
	 */
	function getControlsHTML ($title, $controlsHTML, $borderColor, $backgroundColor, $dividerColor) {
		$halfLineWidth = 1;
		$lineWidth = ($halfLineWidth * 2).'px'; $halfLineWidth = $halfLineWidth.'px';
		ob_start();
		print "\n<div style='"
			."color: #000; "
			."background-color: $backgroundColor; "
			."border-top: $lineWidth solid $borderColor; "
			."border-left: $lineWidth solid $borderColor; "
			."border-right: $lineWidth solid $borderColor;"
			."'"
			." onmouseover='showControlsLink(this)'"
			." onmouseout='hideControlsLink(this)'>";
		print "\n<table border='0' cellpadding='0' cellspacing='0'"
			." style='width: 100%; padding: 0px; margin: 0px; cursor: move;'"
// 			." onmousemove='if(which == 1) { alert(\"dragging\"); drag(this.parentNode, this, screenX, screenY); }'"
// 			." onmouseup='endDrag(this.parentNode)'"
			.">";
		print "\n\t<tr>";
		print "\n\t\t<td>";
		print "\n\t\t".$title;
		print "\n\t\t</td>";
		print "\n\t\t<td style='text-align: right;'>";
		print "\n\t\t\t\t<span class='controls_link'"
			."style='visibility: hidden; cursor: pointer;'"
			." onclick='toggleControls(this.parentNode.parentNode.parentNode.parentNode.parentNode);'>";
		print "\n\t\t\t"._("Show Controls");
		print "\n\t\t\t</span>";
		print "\n\t\t</td>";
		print "\n\t</tr>";
		print "\n</table>";
		
		print "\n\t\t\t<div class='controls' style='display: none; border-top: 1px solid $dividerColor;'>";
		print $controlsHTML;
		print "\n\t\t\t\t</div>";
		
		print "\n</div>";
		
		return ob_get_clean();
	}
}

?>