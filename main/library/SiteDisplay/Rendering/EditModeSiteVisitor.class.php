<?php
/**
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.38 2007/01/15 21:49:35 adamfranco Exp $
 */

require_once(HARMONI."GUIManager/StyleProperties/VerticalAlignSP.class.php");
require_once(dirname(__FILE__)."/ControlsSiteVisitor.class.php");

/**
 * The edit-mode site visitor renders the site for editing, displaying controls.
 * 
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: EditModeSiteVisitor.class.php,v 1.38 2007/01/15 21:49:35 adamfranco Exp $
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
		
		$this->ViewModeSiteVisitor();
		$this->_classNames = array(
			'Block' => _('Block'),
			'NavBlock' => _('Navigation Item'),
			'MenuOrganizer' => _('Menu'),
			'FlowOrganizer' => _('ContentOrganizer'),
			'FixedOrganizer' => _('Organizer'),
			'SubMenu_multipart' => _('Sub-Menu'),
			'ContentPage_multipart' => _('Content Page'),
			'SidebarContentPage_multipart' => _('Content Page with Sidebar')
		);
		
		
	}
	
	/**
	 * Visit a block and return the resulting GUI component.
	 * 
	 * @param object BlockSiteComponent $block
	 * @return object Component 
	 * @access public
	 * @since 1/15/07
	 */
	function &visitBlock ( &$block ) {
		$guiContainer =& new Container (	new YLayout, BLOCK, 1);
		
		$pluginManager =& Services::getService('PluginManager');
		
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
	function &visitFlowOrganizer ( &$organizer ) {		
		$numCells = $organizer->getTotalNumberOfCells();
		
		if ($organizer->getNumRows() == 0)
			$cellsPerPage = $numCells  + 1;
		// If we are limiting to a number of rows, we are paginating.
		else
			$cellsPerPage = $organizer->getNumColumns() * $organizer->getNumRows();
		
		$childGuiComponents = array();
		for ($i = 0; $i < $numCells; $i++) {
			$child =& $organizer->getSubcomponentForCell($i);
			$childGuiComponents[] =& $child->acceptVisitor($this);
		}
		
		// Add the "Append" form to the organizer
		$pluginManager =& Services::getService("PluginManager");
		$childGuiComponents[] =& new UnstyledBlock($this->getAddFormHTML($organizer->getId(), null, $pluginManager->getEnabledPlugins()));
		
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
	 * @since 1/15/07
	 */
	function &visitMenuOrganizer ( &$organizer ) {
		$guiContainer =& parent::visitMenuOrganizer($organizer);
		
		// Add the "Append" form to the organizer
		$allowed = array();
		$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'ContentPage_multipart');
		$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SidebarContentPage_multipart');
		$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SubMenu_multipart');
// 		$allowed[] = new Type('segue', 'edu.middlebury', 'NavBlock');
		$pluginManager =& Services::getService("PluginManager");
		$allowed = array_merge($allowed, $pluginManager->getEnabledPlugins());
		
		$childComponent =& $guiContainer->add(new MenuItem($this->getAddFormHTML($organizer->getId(), null, $allowed), 2), null, '100%', null, TOP);
		
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
		return $harmoni->request->quickURL("site", "editview", array("node" => $id));
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
		$harmoni =& Harmoni::instance();
		print "\n<form action='";
		print $harmoni->request->quickURL('site', 'addComponent', 
				array('returnNode' => RequestContext::value('node'),
					'returnAction' => $this->_action));
		print "' method='post'>";
		
		print "\n\t<input type='hidden' name='".RequestContext::name('organizerId')."' value='".$organizerId."'/>";
		if (!is_null($cellIndex))
			print "\n\t<input type='hidden' name='".RequestContext::name('cellIndex')."' value='".$cellIndex."'/>";
		
		print "\n\t<div style='text-decoration: underline; cursor: pointer; white-space: nowrap;'";
		print "onclick='this.style.display=\"none\"; this.nextSibling.nextSibling.style.display=\"block\";'";
		print ">";
		print "\n\t\t"._("Append New...");
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
}

?>