<?php
/**
 * @since 4/6/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.21 2007/12/20 20:37:31 adamfranco Exp $
 */

require_once(HARMONI."GUIManager/StyleProperties/VerticalAlignSP.class.php");
require_once(dirname(__FILE__)."/ControlsSiteVisitor.class.php");
require_once(dirname(__FILE__)."/HeaderFooterSiteVisitor.class.php");
require_once(HARMONI."GUIManager/Components/UnstyledMenuItem.class.php");

/**
 * The edit-mode site visitor renders the site for editing, displaying controls.
 * 
 * @since 4/6/06
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.21 2007/12/20 20:37:31 adamfranco Exp $
 */
class EditModeSiteVisitor
	extends ViewModeSiteVisitor
{

	/**
	 * The headerId can take three states:
	 * 	null - not searched
	 *  a string - the id of the header
	 *  false - no header element found
	 * 
	 * @var mixed $headerId; 
	 * @access private
	 * @since 9/24/07
	 */
	private $headerId = null;
	
	/**
	 * The footerId can take three states:
	 * 	null - not searched
	 *  a string - the id of the header
	 *  false - no footer element found
	 * 
	 * @var mixed $headerId; 
	 * @access private
	 * @since 9/24/07
	 */
	private $footerId = null;
	
	/**
	 * The headerCellId can take three states:
	 * 	null - not searched
	 *  a string - the id of the header
	 *  false - no header element found
	 * 
	 * @var mixed $headerId; 
	 * @access private
	 * @since 9/24/07
	 */
	private $headerCellId = null;
	
	/**
	 * The footerCellId can take three states:
	 * 	null - not searched
	 *  a string - the id of the header
	 *  false - no footer element found
	 * 
	 * @var mixed $headerId; 
	 * @access private
	 * @since 9/24/07
	 */
	private $footerCellId = null;

	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 4/14/06
	 */
	function __construct () {
		$this->_action = 'editview';
		
		$this->_controlsVisitor = new ControlsSiteVisitor();
		$this->_controlsVisitor->setReturnAction($this->_action);
		
		
		parent::__construct();
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
	 * Set the action to use when rendering
	 * 
	 * @param string $action
	 * @return void
	 * @access public
	 * @since 9/24/07
	 */
	public function setReturnAction ($action) {
		$this->_action = $action;
		$this->_controlsVisitor->setReturnAction($this->_action);
	}
	
	/**
	 * Answer the plugin content for a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 5/23/07
	 */
	function getPluginContent ( $block ) {
		return parent::getPluginContent($block)
				.$block->acceptVisitor($this->_controlsVisitor);
	}
	
	/**
	 * Answer true if plugin controls should be shown.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/24/07
	 */
	function showPluginControls () {
		return false;
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
		
		// Create and return the component
		ob_start();
		
		print "<div>";
		print $block->acceptVisitor($this->_controlsVisitor);
		print "</div>";
		
		$menuItem->setDisplayName($menuItem->getDisplayName().ob_get_clean(), 1);
		
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
		if ($navBlock->isActive()) {
			$menuItems[0]->setPostHTML($navBlock->acceptVisitor($this->_controlsVisitor));
		} else {
			ob_start();
			$this->_controlsVisitor->printReorderForm($navBlock);
			$menuItems[0]->setPostHTML(ob_get_clean());
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
			if ($child) {
				$childGuiComponent = $child->acceptVisitor($this);
				// Filter out false entries returned due to lack of authorization
				if ($childGuiComponent)
					$childGuiComponents[] = $childGuiComponent;
			}
		}
		
		// Add the "Append" form to the organizer
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"), 
			$organizer->getQualifierId()))
		{
			$childGuiComponents[] = new UnstyledBlock($this->getAddFormHTML($organizer->getId(), null));
		}
		
		$resultPrinter = new ArrayResultPrinter($childGuiComponents,
									$organizer->getNumColumns(), $cellsPerPage);
		$resultPrinter->setRenderDirection($organizer->getDirection());
		$resultPrinter->setNamespace('pages_'.$organizer->getId());
		$resultPrinter->addLinksStyleProperty(new MarginTopSP("10px"));
		
		$guiContainer = $resultPrinter->getLayout();
		
		$guiContainer->setPreHTML($organizer->acceptVisitor($this->_controlsVisitor).$guiContainer->getPreHTML($null = null));
		
		
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
			
			$childComponent = $guiContainer->add(new UnstyledMenuItem($this->getAddFormHTML($organizer->getId(), null, 'addMenuContent', true), 2), null, '100%', CENTER, TOP);
			
			// Add a spacer at the end of the menu
			$guiContainer->add(new UnstyledMenuItem("<div> &nbsp; </div>"));
		}
				
		$guiContainer->setPreHTML($organizer->acceptVisitor($this->_controlsVisitor));
		
		
		return $guiContainer;
	}
	
	/**
	 * Answer the form for Adding new components
	 * 
	 * @param string $organizerId
	 * @param integer $cellIndex
	 * @param string $action The action to use for adding the new content
	 * @return string The form HTML
	 * @access public
	 * @since 4/14/06
	 */
	function getAddFormHTML ($organizerId, $cellIndex, $action = 'addContent', $isMenu = FALSE) {
		ob_start();
		$harmoni = Harmoni::instance();

		$params = array(
					'node' => RequestContext::value('node'),
					'returnNode' => RequestContext::value('node'),
					'returnAction' => $this->_action,
					'organizerId' => $organizerId);
		if (!is_null($cellIndex))
			$params['cellIndex'] = $cellIndex;
		
		print "\n\t<div style='white-space: nowrap; text-align: center;'>";
		print "\n\t\t<a href='";
		print $harmoni->request->quickURL('ui1', $action, $params);
		print "'>";
		if ($isMenu)
			print "\n\t\t\t"._("+ Menu Item");
		else
			print "\n\t\t\t"._("+ Content");
		print "\n\t\t</a>";
		print "\n\t</div>";
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
		print "\n\t\t\t"._("Options");
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
		$showControls = _("Options");
		$hideControls = _("Hide Options");
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
	
	/**
	 * Answer true if the SiteComponent passed is the header or footer
	 * 
	 * @param SiteComponent $siteComponent
	 * @return boolean
	 * @access public
	 * @since 9/24/07
	 */
	public function isHeaderOrFooter (SiteComponent $siteComponent) {
		try {
			if ($this->getHeaderId($siteComponent) == $siteComponent->getId())
				return true;
		} catch (Exception $e) {}

		try {		
			if ($this->getFooterId($siteComponent) == $siteComponent->getId())
				return true;
		} catch (Exception $e) {}
			
		return false;
	}
	
	/**
	 * Answer the header id or throw an exception if not found.
	 * 
	 * @param object Site
	 * @return string
	 * @access public
	 * @since 9/24/07
	 */
	public function getHeaderId (SiteComponent $siteComponent) {
		if (is_null($this->headerId))
			$this->searchHeaderFooter($siteComponent);
			
		if ($this->headerId === false)
			throw new Exception("No header found in this site.");
		
		return $this->headerId;
	}
	
	/**
	 * Answer the header id or throw an exception if not found.
	 * 
	 * @param object Site
	 * @return string
	 * @access public
	 * @since 9/24/07
	 */
	public function getFooterId (SiteComponent $siteComponent) {
		if (is_null($this->footerId))
			$this->searchHeaderFooter($siteComponent);
			
		if ($this->footerId === false)
			throw new Exception("No footer found in this site.");
		
		return $this->footerId;
	}
	
	/**
	 * Answer the cell id that the header is in or throw an exception if not found.
	 * 
	 * @param object Site
	 * @return boolean
	 * @access public
	 * @since 9/24/07
	 */
	public function getHeaderCellId (SiteComponent $siteComponent) {
		if (is_null($this->headerCellId))
			$this->searchHeaderFooter($siteComponent);
		
		if ($this->headerCellId === false)
			throw new Exception("No header cell found in this site.");
		
		return $this->headerCellId;
	}
	
	/**
	 * Answer true if there is an empty footer cell.
	 * 
	 * @param object Site
	 * @return boolean
	 * @access public
	 * @since 9/24/07
	 */
	public function getFooterCellId (SiteComponent $siteComponent) {
		if (is_null($this->footerCellId))
			$this->searchHeaderFooter($siteComponent);
		
		if ($this->footerCellId === false)
			throw new Exception("No footer cell found in this site.");
		
		return $this->footerCellId;
	}
	
	/**
	 * Search for a header and footer.
	 * 
	 * @return void
	 * @access public
	 * @since 9/24/07
	 */
	public function searchHeaderFooter (SiteComponent $siteComponent) {
		$siteNavBlock = $this->getSiteNavBlock($siteComponent);
		
		$visitor = new HeaderFooterSiteVisitor($siteNavBlock);
		
		if (is_null($visitor->getHeaderId()))
			$this->headerId = false;
		else
			$this->headerId = $visitor->getHeaderId();
		
		if (is_null($visitor->getFooterId()))
			$this->footerId = false;
		else
			$this->footerId = $visitor->getFooterId();
		
		
		if (is_null($visitor->getHeaderCellId()))
			$this->headerCellId = false;
		else
			$this->headerCellId = $visitor->getHeaderCellId();
		
		if (is_null($visitor->getFooterCellId()))
			$this->footerCellId = false;
		else
			$this->footerCellId = $visitor->getFooterCellId();	
		
		
		if (is_null($this->headerId))
			throw new Exception ("Header search failed.");
		
		if (is_null($this->footerId))
			throw new Exception ("Footer search failed.");
	}
	
	/**
	 * Answer the root of the site
	 * 
	 * @param SiteComponent $siteComponent
	 * @return SiteNavBlockSiteComponent
	 * @access public
	 * @since 9/24/07
	 */
	public function getSiteNavBlock (SiteComponent $siteComponent) {
		$parent = $siteComponent->getParentComponent();
		
		if (is_null($parent))
			return $siteComponent;
		else
			return $this->getSiteNavBlock($parent);
	}
}

?>