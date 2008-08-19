<?php
/**
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.37 2008/04/11 19:48:27 achapin Exp $
 */

require_once(HARMONI."GUIManager/StyleProperties/VerticalAlignSP.class.php");
require_once(dirname(__FILE__)."/EditModeControlsSiteVisitor.class.php");
require_once(HARMONI."GUIManager/Components/UnstyledMenuItem.class.php");

/**
 * The edit-mode site visitor renders the site for editing, displaying controls.
 * 
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.37 2008/04/11 19:48:27 achapin Exp $
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
	function __construct () {
		$this->_action = 'editview';
		
		$this->_controlsVisitor = new EditModeControlsSiteVisitor();
		$this->_controlsVisitor->setReturnAction($this->_action);
		
		
		parent::__construct();
		$this->_classNames = array(
			'Block' => _('Content Block'),
			'NavBlock' => _('Page'),
			'NavSection' => _('Section'),
			'SiteNavBlock' => _('Site'),
			'MenuOrganizer' => _('Pages Container'),
			'FlowOrganizer' => _('Content Container'),
			'FixedOrganizer' => _('Layout Container'),
			'SubMenu_multipart' => _('Section'),
			'SidebarSubMenu_multipart' => _('Section with Sidebar'),
			'ContentPage_multipart' => _('Page'),
			'SidebarContentPage_multipart' => _('Page with Sidebar')
			
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
		$guiContainer = parent::visitBlock($block);
		if ($guiContainer)
			$guiContainer = $this->addBlockControls($block, $guiContainer);			
		
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
	function addBlockControls (BlockSiteComponent $block, Container $guiContainer) {
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
					'#090', '#9F9', '#6C6', 0, true,
					Segue_Selection::instance()->getAddLink($block))				."<br/>";
			$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
			
			$guiContainer->setPostHTML($this->getBarPostHTML());
		}
		
		return $guiContainer;
	}
	
	/**
	 * Answer the title of a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 5/18/07
	 */
	function getBlockTitle ( $block ) {
		ob_start();		
		print "\n<div class='ui2_reorder'>";
		// Look at the order cascading down from the flow organizer.
		if ($block->sortMethod() == 'custom') {
			$this->_controlsVisitor->printReorderLink($block);
			$this->_controlsVisitor->printReorderForm($block);
		}
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$block->getQualifierId()))
		{
			print "\n\t";
			if ($block->sortMethod() == 'custom')
				print " | ";
			print "<a href='".$this->getHistoryUrl($block->getId())."'>";
			print _("history");
			print "</a>";
		}

		print "\n</div>";
		
		print parent::getBlockTitle($block);
		
		return ob_get_clean();
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
	 * Answer the tags for a block
	 * 
	 * @param object BlockSiteComponent $block
	 * @return string
	 * @access public
	 * @since 4/3/08
	 */
	function getTags ( $block ) {
		$harmoni = Harmoni::instance();
		ob_start();	
			
		// Tags
		SiteDispatcher::passthroughContext();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$item = TaggedItem::forId($block->getQualifierId(), 'segue');
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), $block->getQualifierId()))
		{
			print "\n\t<div class='tagging_tags_display'>";
			print TagAction::getTagCloudForItem($item, 'sitetag',
				array(	'font-size: 90%;',
						'font-size: 100%;',
				));		
		} else {
			print TagAction::getTagCloud($item->getTags(), 'sitetag',
				array(	'font-size: 90%;',
						'font-size: 100%;',
				));
		}
		SiteDispatcher::forgetContext();
		print "\n\t</div>";
		return ob_get_clean();
	}
	
	/**
	 * Answer true if the block tags should be shown.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return boolean
	 * @access public
	 * @since 4/3/08
	 */
	function showTags ( $block ) {
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
					'#090', '#9F9', '#6C6', 0, true,
					Segue_Selection::instance()->getAddLink($block))				."<br/>";
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
		
		if ($navBlock->isSection()) {
			$label = $this->_classNames['NavSection'];
		} else {
			$label = $this->_classNames['NavBlock'];
		}
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$navBlock->getQualifierId()))
		{
			$controlsHTML = $this->getBarPreHTML('#090')
				.$this->getControlsHTML(
					"<em>".$label."</em>", 
					$navBlock->acceptVisitor($this->_controlsVisitor), 
					'#090', '#9F9', '#6C6', 0, true,
					Segue_Selection::instance()->getAddLink($navBlock))
				."<br/>";
			$menuItems[0]->setPreHTML($controlsHTML.$menuItems[0]->getPreHTML($null = null));
			
			$menuItems[0]->setPostHTML($this->getBarPostHTML());
		}
		
		return $menuItems;
	}
	
	/**
	 * Answer additional HTML to go after the nav title.
	 * 
	 * @param object  NavBlockSiteComponent $navBlock
	 * @return string
	 * @access public
	 * @since 9/21/07
	 */
	public function getAdditionalNavHTML (NavBlockSiteComponent $navBlock) {
		ob_start();		
		print "\n<div class='ui2_reorder'>";
		$this->_controlsVisitor->printReorderLink($navBlock);
		$this->_controlsVisitor->printReorderForm($navBlock);
		print "\n</div>";
		return ob_get_clean();
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
		$i = 0;
		foreach ($organizer->getSortedSubcomponents() as $child) {
			if ($child) {
				$childGuiComponent = $child->acceptVisitor($this);
				// Filter out false entries returned due to lack of authorization
				if ($childGuiComponent)
					$childGuiComponents[] = $this->addFlowChildWrapper($organizer, $i, $childGuiComponent);
			}
			$i++;
		}
		
		// Add the "Append" form to the organizer
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"), 
			$organizer->getQualifierId()))
		{
		
			$pluginManager = Services::getService("PluginManager");
			
			$formHtml = "\n\t<div class='ui2_add_form_wrapper'>";
			$formHtml .= $this->getAddFormHTML($organizer->getId(), null, $pluginManager->getEnabledPlugins());
			// Move/Copy from selection
			$formHtml .= "\n\t | ".Segue_Selection::instance()->getMoveCopyLink($organizer);
			$formHtml .= "\n\t</div>";
			
			$form = $this->addFlowChildWrapper($organizer, $i, 
				new UnstyledBlock($formHtml));
			
			// Add the form to the beginning of the list for custom ordering or recent last
			if (in_array($organizer->sortMethod(), array('custom', 'create_date_asc', 'mod_date_asc')))
			{
				$childGuiComponents[] = $form;
			} 
			// For sorting modes, put it at the front of the list.
			else {
				array_unshift($childGuiComponents, $form);
			}
		}
		
		
		if (count($childGuiComponents)) {
			$resultPrinter = new ArrayResultPrinter($childGuiComponents,
										$organizer->getNumColumns(), $cellsPerPage);
			$resultPrinter->setRenderDirection($organizer->getDirection());
			$resultPrinter->setNamespace('pages_'.$organizer->getId());
			$resultPrinter->addLinksStyleProperty(new MarginTopSP("10px"));
			
			$guiContainer = $resultPrinter->getLayout();
		} else {
			return null;
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
			$allowed[] = _("Pages and Navigation");
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'ContentPage_multipart');
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SubMenu_multipart');
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SidebarSubMenu_multipart');
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SidebarContentPage_multipart');

	// 		$allowed[] = new Type('segue', 'edu.middlebury', 'NavBlock');
			$allowed[] = _("Content Blocks");
			$pluginManager = Services::getService("PluginManager");
			$allowed = array_merge($allowed, $pluginManager->getEnabledPlugins());
			
			
			$formHtml = "\n\t<div class='ui2_add_form_wrapper'>";
			$formHtml .= $this->getAddFormHTML($organizer->getId(), null, $allowed, true);
			// Move/Copy from selection
			$formHtml .= "\n\t | ".Segue_Selection::instance()->getMoveCopyLink($organizer);
			$formHtml .= "\n\t</div>";
			
			$childComponent = $guiContainer->add($this->addFlowChildWrapper($organizer, $organizer->getTotalNumberOfCells(), 
				new UnstyledMenuItem($formHtml, 2)), null, '100%', null, TOP);
				
			// Add a spacer at the end of the menu
			$guiContainer->add(new UnstyledMenuItem("<div> &nbsp; </div>"));
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
	 * Answer a placeholder for a menu target
	 * 
	 * @param object MenuOrganizerSiteComponent $organizer
	 * @return Component
	 * @access protected
	 * @since 12/18/07
	 */
	protected function getMenuTargetPlaceholder (MenuOrganizerSiteComponent $organizer) {
		// Add a placeholder to our target if we don't have any children
		ob_start();
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idMgr->getId("edu.middlebury.authorization.add_children"),
			$organizer->getQualifierId()))
		{
			print "<div style='height: 50px; border: 1px solid #F00; margin: 0px 5px 5px 5px; padding: 5px;'>";
			print _("This Menu has no Content Pages yet. <br/><br/>Add a Content Page by clicking the <strong>+ Menu Item</strong> button for this Menu and choose 'Content Page'.");
		} else {
			print "<div style='height: 50px; margin: 0px 5px 5px 5px; padding: 5px;'>";
			print " ";
		}
		print "\n</div>";
		$placeholder = new UnstyledBlock(ob_get_clean());
		
		return $placeholder;
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
	function getAddFormHTML ($organizerId, $cellIndex, $allowed, $isMenu = FALSE) {
		ob_start();
		$harmoni = Harmoni::instance();
		print "\n\t<a style='text-align: center; display: inline;'";
		print " onclick='this.style.display=\"none\"; this.nextSibling.style.display=\"block\";'";
		print ">";
		if ($isMenu)
			print "\n\t\t\t"._("+ Page...");
		else
			print "\n\t\t\t"._("+ Content");
		print "\n\t</a>";
		print "<form action='";
		print $harmoni->request->quickURL('ui2', 'addComponent', 
				array('returnNode' => SiteDispatcher::getCurrentNodeId(),
					'returnAction' => $this->_action));
		print "' method='post' ";
		print " style='display: none' class='ui2_add_form'>";
		
		print "\n\t<input type='hidden' name='".RequestContext::name('organizerId')."' value='".$organizerId."'/>";
		if (!is_null($cellIndex))
			print "\n\t<input type='hidden' name='".RequestContext::name('cellIndex')."' value='".$cellIndex."'/>";
		//print "\n\t<div class='block2Content' style='text-align: center;'";		
		print "\n\t\t<select name='".RequestContext::name('componentType')."'>";
		
		$inCat = false;
		foreach ($allowed as $type) {
			if (is_string($type)) {
				if ($inCat) {
					print "\n\t\t\t</optgroup>";
				}
				$inCat = true;
				print "\n\t\t\t<optgroup label='$type'>";
			} else {
				$this->printTypeOption($type);
			}
		}
		if ($inCat)
			print "\n\t\t\t</optgroup>";
		
		print "\n\t\t</select>";
		print "\n\t\t<div style='white-space: nowrap;'>"._("Title: ");
		print "\n\t\t\t<input name='".RequestContext::name('displayName')."' type='text' size='10'/>";
		print "\n\t\t</div>";
		
		print "\n\t\t<div style='white-space: nowrap; margin: 5px;'>";
		print "\n\t\t\t<input type='button' value='"._('Submit')."'";
		print " onclick='";
		print "var hasTitle = false; ";
		print "var regex = /[^\\s\\n\\t]+/; ";
		print "for (var i = 0; i < this.form.elements.length; i++) { ";
		print 		"var elem = this.form.elements[i]; ";
		print 		"if (elem.name == \"".RequestContext::name('displayName')."\") { ";
		print 			"if (elem.value.match(regex)) { ";
		print 				"hasTitle = true;";
		print 			"}";
		print 		"}";
		print "}";
		print "if (!hasTitle) { ";
		print 		"alert(\""._("A title is required")."\");";
		print "} else { ";
		print 	"this.form.submit();";
		print "}";
		print "' />";
		print "\n\t\t\t<input type='button' ";
		print "onclick='this.parentNode.parentNode.style.display=\"none\"; this.parentNode.parentNode.previousSibling.style.display=\"inline\";'";
		print " value='"._("Cancel")."'/>";
		print "\n\t\t</div>";
		print "</form>";
		return ob_get_clean();
	}
	
	/**
	 * print an option tag
	 * 
	 * @param object Type $type
	 * @return void
	 * @access private
	 * @since 12/14/07
	 */
	private function printTypeOption (Type $type) {
		print "\n\t\t\t<option value='".$type->asString()."'>";
		if (isset($this->_classNames[$type->getKeyword()]))
			print $this->_classNames[$type->getKeyword()];
		else {
			try {
				$pluginManager = Services::getService("PluginManager");
				$class = $pluginManager->getPluginClass($type);
				print call_user_func(array($class, 'getPluginDisplayName'));
			} catch (UnknownIdException $e) {
				print $type->getKeyword();
			}
		}
		print "</option>";
	}
	
	/**
	 * Answer the HTML for the controls top-bar
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 4/7/06
	 */
	function getControlsHTML ($title, $controlsHTML, $borderColor, $backgroundColor, $dividerColor, $leftIndentLevel = 0, $float = 0, $selectionLinkHtml = null) {
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
		
		if (!$this->controlsAlwaysVisible())
			print "visibility: hidden; ";
		print "position: absolute; ";
		print "z-index: 10; ";
		print "left: 0px; ";
		
		
		print "'";
		print " onmouseover='showControlsLink(this)'"
			." onmouseout='hideControlsLink(this)'";
		print ">";
		print "\n<table border='0' cellpadding='0' cellspacing='0'"
			." style='width: 100%; padding: 0px; margin: 0px; "
			."background-color: $backgroundColor; "
			.$opacityStyles
			."'"
// 			." onclick='toggleControls(this.parentNode.parentNode);'"

			.">";
		print "\n\t<tr>";
		print "\n\t\t<td class='controls_bar_title'>";
		print "\n\t\t".$title;
		print "\n\t\t</td>";
		print "\n\t\t<td style='text-align: right;'>";
		if (!is_null($selectionLinkHtml)) {
			print "\n\t\t\t\t<span class='selection_link'"
			." style='visibility: hidden; cursor: pointer; white-space: nowrap;'"
			.">";
			print $selectionLinkHtml;
			print " | </span>";
		}
		print "\n\t\t\t\t<span class='controls_link'"
			." style='visibility: hidden; cursor: pointer; white-space: nowrap;'"
			." onclick='toggleControls(this.parentNode.parentNode.parentNode.parentNode.parentNode);'"
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
	 * Answer true if borders and controls should be always visible
	 *
	 * @return boolean
	 * @access protected
	 * @since 12/17/07
	 */
	protected function controlsAlwaysVisible () {
		return false;
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
		if (!$this->controlsAlwaysVisible()) {
			print " onmouseover='this.borderColor = \"$borderColor\"; showControls(this)'";
			print " onmouseout='if (isValidMouseOut(this, event)) {hideControls(this);} '";
			print " style='position: relative; border: 2px solid transparent;'";
		} else {
			print " style='position: relative; border: 2px solid $borderColor;'";
		}
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
		
		// Show the selection link as well
		var selectionLink = getDescendentByClassName(mainElement, 'selection_link');
		if (selectionLink)
				selectionLink.style.visibility = 'visible';
	}
	
	function hideControlsLink(mainElement) {
		var controls = getDescendentByClassName(mainElement, 'controls');
		if (controls.style.display != 'block') {
			var controlsLink = getDescendentByClassName(mainElement, 'controls_link');
			controlsLink.style.visibility = 'hidden';
			
			// Hide the selection link as well
			var selectionLink = getDescendentByClassName(mainElement, 'selection_link');
			if (selectionLink)
				selectionLink.style.visibility = 'hidden';
		}		
	}
	
	function toggleControls(mainElement) {
		var controls = getDescendentByClassName(mainElement, 'controls');
		// if controls aren't show, show them
		if (controls.style.display != 'block') {
			controls.style.display = 'block';
			mainElement.style.zIndex = '11';
			
			var controlsLink = getDescendentByClassName(mainElement, 'controls_link');
			controlsLink.style.visibility = 'visible';
			controlsLink.innerHTML = '$hideControls';
		}
		// if they are shown, hide them.
		else {
			var controls = getDescendentByClassName(mainElement, 'controls');
			controls.style.display = 'none';
			mainElement.style.zIndex = '10';
			
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