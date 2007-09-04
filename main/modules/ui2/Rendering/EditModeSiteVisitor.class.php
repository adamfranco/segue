<?php
/**
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.10 2007/09/04 15:07:44 adamfranco Exp $
 */

require_once(HARMONI."GUIManager/StyleProperties/VerticalAlignSP.class.php");
require_once(dirname(__FILE__)."/EditModeControlsSiteVisitor.class.php");

/**
 * The edit-mode site visitor renders the site for editing, displaying controls.
 * 
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.10 2007/09/04 15:07:44 adamfranco Exp $
 */
class EditModeSiteVisitor
	extends ViewModeSiteVisitor
{

	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 4/14/06
	 */
	function EditModeSiteVisitor () {
		$this->_action = 'editview';
		
		$this->_controlsVisitor = new EditModeControlsSiteVisitor();
		$this->_controlsVisitor->setReturnAction($this->_action);
		
		
		$this->ViewModeSiteVisitor();
		$this->_classNames = array(
			'Block' => _('Block'),
			'NavBlock' => _('Nav. Item'),
			'SiteNavBlock' => _('Site'),
			'MenuOrganizer' => _('Menu'),
			'FlowOrganizer' => _('ContentOrganizer'),
			'FixedOrganizer' => _('Organizer'),
			'SubMenu_multipart' => _('Sub-Menu'),
			'SidebarSubMenu_multipart' => _('Sub-Menu with Sidebar'),
			'ContentPage_multipart' => _('Content Page'),
			'SidebarContentPage_multipart' => _('Content Page with Sidebar')
			
		);
		
		ob_start();
		// Print out Javascript functions needed by our methods
		$this->printJavascript();
		
		print<<<END
			
			<style type='text/css'>
				.controls_form {
					text-align: left;
					color: #000;
					padding: 3px;
				}
				
				.controls_form a {
					text-align: left;
					color: #000;
				}
			</style>

END;
		
		
		
		$harmoni = Harmoni::instance();
		$outputHandler = $harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead().ob_get_clean());
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object Component 
	 * @access public
	 * @since 1/15/07
	 */
	public function visitBlock ( BlockSiteComponent $block ) {
		$guiContainer = $this->addBlockControls($block, parent::visitBlock($block));			
		
		return $guiContainer;
	}
	
	/**
	 * Add controls to the block
	 * 
	 * @param object BlockSiteComponent $block
	 * @param object Container $guiContainer
	 * @return object Container The guiContainer
	 * @access public
	 * @since 5/24/07
	 */
	function addBlockControls ($block, $guiContainer) {
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$block->getQualifierId()))
		{
			$controlsHTML = $this->getBarPreHTML('#090')
				.$this->getControlsHTML(
					"<em>".$this->_classNames['Block']."</em>", 
					$block->acceptVisitor($this->_controlsVisitor), 
					'#090', '#9F9', '#6C6', 0, true);
			$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
			
			$guiContainer->setPostHTML($this->getBarPostHTML());
		}
		
		return $guiContainer;
	}
	
	/**
	 * Answer true if plugin controls should be shown.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function showPluginControls () {
		return true;
	}
	
	/**
	 * Visit a block and return the resulting GUI component. (A menu item)
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object MenuItem 
	 * @access public
	 * @since 4/3/06
	 */
	public function visitBlockInMenu ( BlockSiteComponent $block ) {
		$menuItem = parent::visitBlockInMenu($block);
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$block->getQualifierId()))
		{
			$controlsHTML = $this->getBarPreHTML('#090')
				.$this->getControlsHTML(
					"<em>".$this->_classNames['Block']."</em>", 
					$block->acceptVisitor($this->_controlsVisitor), 
					'#090', '#9F9', '#6C6', 0, true);
			$menuItem->setPreHTML($controlsHTML.$menuItem->getPreHTML($null = null));
			
			$menuItem->setPostHTML($this->getBarPostHTML());
		}
		
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
	public function visitNavBlock ( NavBlockSiteComponent $navBlock ) {
		$menuItems = parent::visitNavBlock($navBlock);
		
		if (!$menuItems)
			return $menuItems;
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$navBlock->getQualifierId()))
		{
			$controlsHTML = $this->getBarPreHTML('#090')
				.$this->getControlsHTML(
					"<em>".$this->_classNames['NavBlock']."</em>", 
					$navBlock->acceptVisitor($this->_controlsVisitor), 
					'#090', '#9F9', '#6C6', 0, true)
				."<br/>";
			$menuItems[0]->setPreHTML($controlsHTML.$menuItems[0]->getPreHTML($null = null));
			
			$menuItems[0]->setPostHTML($this->getBarPostHTML());
		}
		
		return $menuItems;
	}
	
	
	/**
	 * Visit a menu organizer and return the menu GUI component that corresponds
	 * to it.
	 * 
	 * @param object MenuOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 1/15/07
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {		
		$numCells = $organizer->getTotalNumberOfCells();
		
		if ($organizer->getNumRows() == 0)
			$cellsPerPage = $numCells  + 1;
		// If we are limiting to a number of rows, we are paginating.
		else
			$cellsPerPage = $organizer->getNumColumns() * $organizer->getNumRows();
		
		$childGuiComponents = array();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $organizer->getSubcomponentForCell($i);
			$childGuiComponent = $child->acceptVisitor($this);
			// Filter out false entries returned due to lack of authorization
			if ($childGuiComponent)
				$childGuiComponents[] = $childGuiComponent;
		}
		
		// Add the "Append" form to the organizer
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"), 
			$organizer->getQualifierId()))
		{
			$pluginManager = Services::getService("PluginManager");
			$childGuiComponents[] = new UnstyledBlock($this->getAddFormHTML($organizer->getId(), null, $pluginManager->getEnabledPlugins()));
		}
		
		$resultPrinter = new ArrayResultPrinter($childGuiComponents,
									$organizer->getNumColumns(), $cellsPerPage);
		$resultPrinter->setRenderDirection($organizer->getDirection());
		$resultPrinter->setNamespace('pages_'.$organizer->getId());
		$resultPrinter->addLinksStyleProperty(new MarginTopSP("10px"));
		
		$guiContainer = $resultPrinter->getLayout();
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$organizer->getQualifierId()))
		{
			$controlsHTML = $this->getBarPreHTML('#00F')
				.$this->getControlsHTML(
					"<em>".$this->_classNames['FlowOrganizer']."</em>", 
					$organizer->acceptVisitor($this->_controlsVisitor), 
					'#00F', '#99F', '#66F');
			$guiContainer->setPreHTML($controlsHTML."\n<div style='z-index: 0;'>".$guiContainer->getPreHTML($null = null));
			
			$guiContainer->setPostHTML($guiContainer->getPostHTML($null = null)."</div>".$this->getBarPostHTML());
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
	 * @since 1/15/07
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {
		$guiContainer = parent::visitMenuOrganizer($organizer);
		
		// Add the "Append" form to the organizer
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"), 
			$organizer->getQualifierId()))
		{
			$allowed = array();
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'ContentPage_multipart');
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SidebarContentPage_multipart');
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SubMenu_multipart');
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SidebarSubMenu_multipart');
	// 		$allowed[] = new Type('segue', 'edu.middlebury', 'NavBlock');
			$pluginManager = Services::getService("PluginManager");
			$allowed = array_merge($allowed, $pluginManager->getEnabledPlugins());
			
			$childComponent = $guiContainer->add(new MenuItem($this->getAddFormHTML($organizer->getId(), null, $allowed), 2), null, '100%', null, TOP);
		}
				
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$organizer->getQualifierId()))
		{
			$controlsHTML = $this->getBarPreHTML('#00F')
				.$this->getControlsHTML(
					"<em>".$this->_classNames['MenuOrganizer']."</em>", 
					$organizer->acceptVisitor($this->_controlsVisitor), 
					'#00F', '#99F', '#66F');
			$guiContainer->setPreHTML($controlsHTML."\n<div style='z-index: 0;'>".$guiContainer->getPreHTML($null = null));
			
			$guiContainer->setPostHTML($guiContainer->getPostHTML($null = null)."</div>".$this->getBarPostHTML());
		}
		
		return $guiContainer;
	}
	
	/**
	 * Answer the form for Adding new components
	 * 
	 * @param string $organizerId
	 * @param integer $cellIndex
	 * @param array $allowed Which component Types to allow addition of: segue::edu.middlebury::Block, segue::edu.middlebury::NavBlock
	 * @return string The form HTML
	 * @access public
	 * @since 4/14/06
	 */
	function getAddFormHTML ($organizerId, $cellIndex, $allowed) {
		ob_start();
		$harmoni = Harmoni::instance();
		print "\n<form action='";
		print $harmoni->request->quickURL('ui2', 'addComponent', 
				array('returnNode' => RequestContext::value('node'),
					'returnAction' => $this->_action));
		print "' method='post'>";
		
		print "\n\t<input type='hidden' name='".RequestContext::name('organizerId')."' value='".$organizerId."'/>";
		if (!is_null($cellIndex))
			print "\n\t<input type='hidden' name='".RequestContext::name('cellIndex')."' value='".$cellIndex."'/>";
		//print "\n\t<div class='block2Content' style='text-align: center;'";
		print "\n\t<div style='text-align: center;'";
		print "onclick='this.style.display=\"none\"; this.nextSibling.nextSibling.style.display=\"block\";'";
		print ">";
		print "\n\t\t"._("Append new...");
		print "\n\t</div>";
		print "\n\t<div style='display: none'>";
		
		print "\n\t\t<select name='".RequestContext::name('componentType')."'>";
		
		foreach ($allowed as $type) {
			print "\n\t\t\t<option value='".$type->asString()."'>";
			if (isset($this->_classNames[$type->getKeyword()]))
				print $this->_classNames[$type->getKeyword()];
			else
				print $type->getKeyword();
			print "</option>";
		}
		
		print "\n\t\t</select>";
		print "\n\t\t<div style='white-space: nowrap;'>"._("Title: ");
		print "\n\t\t\t<input name='".RequestContext::name('displayName')."' type='text' size='10'/>";
		print "\n\t\t</div>";
		
		print "\n\t\t<div style='white-space: nowrap; text-align: right;'>";
		print "\n\t\t\t<input type='button' value='"._('Submit')."'";
		print " onclick='";
		print "var hasTitle = false; ";
		print "var regex = /[^\\s\\n\\t]+/; ";
		print "for (var i = 0; i < this.form.elements.length; i++) { ";
		print 		"var elem = this.form.elements[i]; ";
		print 		"if (elem.name == \"".RequestContext::name('displayName')."\" && elem.value.match(regex)) {";
		print 			"hasTitle = true;";
		print 		"}";
		print "}";
		print "if (!hasTitle) { ";
		print 		"alert(\""._("A title is required")."\");";
		print "} else { ";
		print 	"this.form.submit();";
		print "}";
		print "' />";
		print "\n\t\t\t<input type='button' ";
		print "onclick='this.parentNode.parentNode.style.display=\"none\"; this.parentNode.parentNode.previousSibling.previousSibling.style.display=\"block\";'";
		print " value='"._("Cancel")."'/>";
		print "\n\t\t</div>";
		print "\n\t</div>";
		print "</form>";
		return ob_get_clean();
	}
	
	/**
	 * Answer the HTML for the controls top-bar
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 4/7/06
	 */
	function getControlsHTML ($title, $controlsHTML, $borderColor, $backgroundColor, $dividerColor, $leftIndentLevel = 0, $float = 0) {
		$halfLineWidth = 1;
		$lineWidth = ($halfLineWidth * 2).'px'; $halfLineWidth = $halfLineWidth.'px';
		
		$opacityStyles =	"filter:alpha(opacity=70); "
							."-moz-opacity: .70; "
							."opacity: .70; ";
		ob_start();
		print "\n<div class='controls_bar' style='"
			."color: #000; "
			."min-width: 200px; "
// 			."border-top: $lineWidth solid $borderColor; "
// 			."border-left: $lineWidth solid $borderColor; "
// 			."border-right: $lineWidth solid $borderColor; "
			.(($leftIndentLevel)?"margin-left: 10px; ":"");
		
		print "visibility: hidden; ";
		print "position: absolute; ";
		print "z-index: 9999; ";
		print "left: 0px; ";
		
		
		print "'";
		print " onmouseover='showControlsLink(this)'"
			." onmouseout='hideControlsLink(this)'";
		print ">";
		print "\n<table border='0' cellpadding='0' cellspacing='0'"
			." style='width: 100%; padding: 0px; margin: 0px; cursor: pointer;"
			."background-color: $backgroundColor; "
			.$opacityStyles
			."'"
			." onclick='toggleControls(this.parentNode.parentNode);'"
			.">";
		print "\n\t<tr>";
		print "\n\t\t<td>";
		print "\n\t\t".$title;
		print "\n\t\t</td>";
		print "\n\t\t<td style='text-align: right;'>";
		print "\n\t\t\t\t<span class='controls_link'"
			."style='visibility: hidden; cursor: pointer; white-space: nowrap;'"
			.">";
		print "\n\t\t\t"._("Show Controls");
		print "\n\t\t\t</span>";
		print "\n\t\t</td>";
		print "\n\t</tr>";
		print "\n</table>";
		
		$opacityStyles =	"filter:alpha(opacity=95); "
							."-moz-opacity: .95; "
							."opacity: .95; ";
		
		print "\n\t\t\t<div class='controls' style='display: none; border-top: 1px solid $dividerColor; background-color: $backgroundColor; ".$opacityStyles."'>";
		print $controlsHTML;
		print "\n\t\t\t\t</div>";
		
		print "\n</div>";
		if (!$float) {
			print "\n<div style='display: block;' class='controls_spacer'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</div>";
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Answer a wrapping div that triggers showing and hiding the border and 
	 * controls-bar for the item
	 * 
	 * @param string $borderColor
	 * @return string
	 * @access public
	 * @since 1/16/07
	 */
	function getBarPreHTML ($borderColor) {
		ob_start();
		print "\n<div class='site_component_wrapper'";
		print " onmouseover='this.borderColor = \"$borderColor\"; showControls(this)'";
		print " onmouseout='if (isValidMouseOut(this, event)) {hideControls(this);} '";
		print " style='position: relative; border: 2px solid transparent;'";
		print ">";
		return ob_get_clean();
	}
	
	/**
	 * Answer a wrapping div that triggers showing and hiding the border and 
	 * controls-bar for the item
	 * 
	 * @param string $borderColor
	 * @return string
	 * @access public
	 * @since 1/16/07
	 */
	function getBarPostHTML () {
		return "\n</div>";
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

	function showControls(mainElement) {
 		mainElement.style.borderColor = mainElement.borderColor;
		var controls = getDescendentByClassName(mainElement, 'controls_bar');
		controls.style.visibility = 'visible';
		
		var spacer = getDescendentByClassName(mainElement, 'controls_spacer');
		if (spacer)
			spacer.style.visibility = 'visible';
		
		// First extend the main element
		var rightEdge = document.getOffsetLeft(controls) + controls.offsetWidth;
		var windowSize = getWindowDimensions();
		var windowScroll = getScrollXY();
		var windowRight = windowSize[0] + windowScroll[0];
		// Scroll over to the show the full bar
		if (windowRight < rightEdge) {
			mainElement.style.width = controls.offsetWidth + 'px';
		}
		
		// Scroll over if necessary
		var rightEdge = document.getOffsetLeft(controls) + controls.offsetWidth;
		var windowSize = getWindowDimensions();
		var windowScroll = getScrollXY();
		var windowRight = windowSize[0] + windowScroll[0];
		// Scroll over to the show the full bar
		if (windowRight < rightEdge) {
			window.scrollBy(rightEdge - windowRight, 0);
		}
	}
	
	function hideControls(mainElement) {
		var controls = getDescendentByClassName(mainElement, 'controls');
		if (controls.style.display != 'none') {
			return;
		}
		mainElement.style.borderColor = 'transparent';
		var controls = getDescendentByClassName(mainElement, 'controls_bar');
		controls.style.visibility = 'hidden';
		var spacer = getDescendentByClassName(mainElement, 'controls_spacer');
// 		if (spacer)
// 			spacer.style.visibility = 'hidden';
		
		mainElement.style.width = '';
	}
		
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
	
	//--------------------------
	// By Mark "Tarquin" wilton-Jones
	// From: http://www.howtocreate.co.uk/tutorials/javascript/browserwindow
	//--------------------------
	function getWindowDimensions() {
		var myWidth = 0, myHeight = 0;
		if( typeof( window.innerWidth ) == 'number' ) {
			//Non-IE
			myWidth = window.innerWidth;
			myHeight = window.innerHeight;
		} else if( document.documentElement && ( document.documentElement.clientWidth || document.documentElement.clientHeight ) ) {
			//IE 6+ in 'standards compliant mode'
			myWidth = document.documentElement.clientWidth;
			myHeight = document.documentElement.clientHeight;
		} else if( document.body && ( document.body.clientWidth || document.body.clientHeight ) ) {
			//IE 4 compatible
			myWidth = document.body.clientWidth;
			myHeight = document.body.clientHeight;
		}
		
		return [ myWidth, myHeight ];
	}
	
	//--------------------------
	// By Mark "Tarquin" wilton-Jones
	// From: http://www.howtocreate.co.uk/tutorials/javascript/browserwindow
	//--------------------------
	function getScrollXY() {
		var scrOfX = 0, scrOfY = 0;
		if( typeof( window.pageYOffset ) == 'number' ) {
			//Netscape compliant
			scrOfY = window.pageYOffset;
			scrOfX = window.pageXOffset;
		} else if( document.body && ( document.body.scrollLeft || document.body.scrollTop ) ) {
			//DOM compliant
			scrOfY = document.body.scrollTop;
			scrOfX = document.body.scrollLeft;
		} else if( document.documentElement && ( document.documentElement.scrollLeft || document.documentElement.scrollTop ) ) {
			//IE6 standards compliant mode
			scrOfY = document.documentElement.scrollTop;
			scrOfX = document.documentElement.scrollLeft;
		}
		return [ scrOfX, scrOfY ];
	}
	
/* ]]> */
</script>

END;
	}
}

?>