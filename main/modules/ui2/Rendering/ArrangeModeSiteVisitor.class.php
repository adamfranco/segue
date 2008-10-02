<?php
/**
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ArrangeModeSiteVisitor.class.php,v 1.24 2008/04/09 21:12:03 adamfranco Exp $
 */

require_once(HARMONI."GUIManager/StyleProperties/VerticalAlignSP.class.php");
require_once(dirname(__FILE__)."/ControlsSiteVisitor.class.php");
require_once(dirname(__FILE__)."/EditModeSiteVisitor.class.php");

/**
 * The edit-mode site visitor renders the site for editing, displaying controls.
 * 
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ArrangeModeSiteVisitor.class.php,v 1.24 2008/04/09 21:12:03 adamfranco Exp $
 */
class ArrangeModeSiteVisitor
	extends EditModeSiteVisitor
{

	/**
	 * Constructor
	 * 
	 * @return object
	 * @access public
	 * @since 4/14/06
	 */
	function __construct () {
		parent::__construct();
		$this->_action = 'arrangeview';
		
		$this->_controlsVisitor = new ControlsSiteVisitor();
		$this->_controlsVisitor->setReturnAction($this->_action);
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
	public function visitSiteNavBlock ( SiteNavBlockSiteComponent $siteNavBlock ) {
		$childGuiComponent = parent::visitSiteNavBlock($siteNavBlock);
		
		
		// enter links in our head to load needed javascript libraries
		$harmoni = Harmoni::instance();
		$outputHandler = $harmoni->getOutputHandler();
		$outputHandler->setHead(
			$outputHandler->getHead()
			."
		<script src='".MYPATH."/javascript/scriptaculous-js/lib/prototype.js' type='text/javascript'></script>
		<script src='".MYPATH."/javascript/scriptaculous-js/src/scriptaculous.js' type='text/javascript'></script>
		
		<script type='text/javascript'>
		// <![CDATA[
		
			Droppables.isAffected = function(point, element, drop) {
				return (
				  (drop.element!=element) &&
				  ((!drop._containers) ||
					this.isContained(element, drop)) &&
				  ((!drop.accept) ||
					(drop.accept.include(element.id)) ||
				  (Element.classNames(element).detect( 
					function(v) { return drop.accept.include(v) } ) )) &&
				  Position.within(drop.element, point[0], point[1]) );
			  };
		
		// ]]>
		</script>

		<style type='text/css'>
			.drop_hover {
				border: 4px inset #F00;
			}
		</style>
");
		
		// Print out Javascript functions needed by our methods
		$this->printJavascript();
		
		// Any further empty cells in fixed organizers should get controls to
		// add to them.
		$allowed = array();
		$allowed[] = new Type('segue', 'edu.middlebury', 'FlowOrganizer');
		$allowed[] = new Type('segue', 'edu.middlebury', 'FixedOrganizer');
		foreach (array_keys($this->_emptyCellContainers) as $id) {
			preg_match("/(.+)_cell:([0-9]+)/", $id, $matches);
			$organizerId = $matches[1];
			$cellIndex = $matches[2];
			
			$this->_emptyCellContainers[$id]->insertAtPlaceholder(
				$this->_emptyCellPlaceholders[$id],
				new UnstyledBlock($this->getInsertFormHTML(
					$siteNavBlock->getDirector(), 
					$organizerId, $cellIndex, $allowed)),
				null, '100%', null, TOP);
			
			unset($this->_emptyCellContainers[$id], $this->_emptyCellPlaceholders[$id], $matches, $organizerId, $cellIndex);
		}
		
		return $childGuiComponent;
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
		parent::addBlockControls($block, $guiContainer);
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$block->getQualifierId()))
		{
			if (count($block->getVisibleDestinationsForPossibleAddition()))
				$this->wrapAsDraggable($guiContainer, $block->getId(), 'Block');
		}
		
		return $guiContainer;
	}
	
	/**
	 * Answer the detail url of a block
	 * 
	 * @param string $id
	 * @return string
	 * @access public
	 * @since 5/18/07
	 */
	function getDetailUrl ($id) {
		$harmoni = Harmoni::instance();
		return SiteDispatcher::quickURL(
				$harmoni->request->getRequestedModule(),
				'editview',
				array("node" => $id));
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
		$guiContainer = parent::visitBlockInMenu($block);
		
		if (!$guiContainer) {
			$false = false;
			return $false;
		}
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$block->getQualifierId()))
		{
			if (count($block->getVisibleDestinationsForPossibleAddition()))
				$this->wrapAsDraggable($guiContainer, $block->getId(), 'NavBlock');
		}
		
		return $guiContainer;
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
		$guiContainers = parent::visitNavBlock($navBlock);
		
		if (!$guiContainers || !count($guiContainers)) {
			$false = false;
			return $false;
		}
		
		$guiContainer = $guiContainers[0];
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$navBlock->getQualifierId()))
		{
			
			if (count($navBlock->getVisibleDestinationsForPossibleAddition()))
				$this->wrapAsDraggable($guiContainer, $navBlock->getId(), 'NavBlock');
		}
		
		return $guiContainers;
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
	public function visitFixedOrganizer ( FixedOrganizerSiteComponent $organizer ) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$organizer->getQualifierId()))
		{
			$tdStyles = 'border: 1px solid #F00; padding: 6px;';
		} else {
			$tdStyles = '';
		}
		$guiContainer = new Container (new TableLayout(
												$organizer->getNumColumns(), 
												$tdStyles),
										BLANK,
										1);
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"), 
			$organizer->getQualifierId()))
		{
			$canAdd = true;
		} else {
			$canAdd = false;
		}
		
		$numCells = $organizer->getTotalNumberOfCells();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $organizer->getSubcomponentForCell($i);
			if (is_object($child)) {
				$childComponent = $child->acceptVisitor($this);
				if ($childComponent)
					$guiContainer->add($childComponent,	$child->getWidth(), null, null, TOP);
				else
					$childComponent = $guiContainer->add(new Blank, $child->getWidth(), null, null, TOP);
			} else {				
				$this->_emptyCellContainers[$organizer->getId().'_cell:'.$i] = $guiContainer;
				$this->_emptyCellPlaceholders[$organizer->getId().'_cell:'.$i] = $guiContainer->addPlaceholder();
				$childComponent = $guiContainer->getComponent(
					$this->_emptyCellPlaceholders[$organizer->getId().'_cell:'.$i]);
			}
			
			if ($canAdd) {
				$this->wrapAsDroppable($childComponent, 
					$organizer->getId()."_cell:".$i,
					array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
			}
		}
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$organizer->getQualifierId()))
		{			
			$controlsHTML = $this->getBarPreHTML('#F00', $organizer)
				.$this->getControlsHTML(
					$organizer,
					$organizer->getDisplayName(), 
					$organizer->acceptVisitor($this->_controlsVisitor), 
					'#F00', '#F99', '#F66');
			$guiContainer->setPreHTML($controlsHTML."\n<div style='z-index: 0;'>".$guiContainer->getPreHTML($null = null));
			
			$guiContainer->setPostHTML($guiContainer->getPostHTML($null = null)."</div>".$this->getBarPostHTML());
			
			if (count($organizer->getVisibleDestinationsForPossibleAddition()))
				$this->wrapAsDraggable($guiContainer, $organizer->getId(), 'FixedOrganizer');
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
	public function visitNavOrganizer ( NavOrganizerSiteComponent $organizer ) {
		return $this->visitFixedOrganizer($organizer);
	}
	
	/**
	 * Add any needed markup to a gui component that is the child of a flow organizer
	 * 
	 * @param object FlowOrganizerSiteComponent $organizer
	 * @param integer $cellIndex
	 * @param object Component $guiComponent
	 * @return object Component
	 * @access protected
	 * @since 12/18/07
	 */
	protected function addFlowChildWrapper (FlowOrganizerSiteComponent $organizer, $cellIndex, Component $guiComponent) {
		$this->wrapAsDroppable($guiComponent, 
					$organizer->getId()."_cell:".$cellIndex,
					array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($cellIndex)));
		return $guiComponent;
	}
	
	/**
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		$guiContainer = parent::visitFlowOrganizer($organizer);
		
		// Controls and organizer dragging.
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$organizer->getQualifierId()))
		{
			if (count($organizer->getVisibleDestinationsForPossibleAddition()))
				$this->wrapAsDraggable($guiContainer, $organizer->getId(), 'FlowOrganizer');
		}
		
		return $guiContainer;
	}
	
	/**
	 * Visit a menu organizer and return the menu GUI component that corresponds
	 * to it.
	 * 
	 * @param object MenuOrganizerSiteComponent $organizer
	 * @return object Component
	 * @access publicZ
	 * @since 4/3/06
	 */
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {
		$guiContainer = parent::visitMenuOrganizer($organizer);
// 		return $guiContainer;
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$organizer->getQualifierId()))
		{	
			if (count($organizer->getVisibleDestinationsForPossibleAddition()))
				$this->wrapAsDraggable($guiContainer, $organizer->getId(), 'FlowOrganizer');
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
		print "<div style='height: 50px;'>";
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idMgr->getId("edu.middlebury.authorization.add_children"),
			$organizer->getQualifierId()))
		{
			print _("This Section has no Pages yet. <br/><br/>Add a Page by clicking the <strong>+ Page</strong> button for this Section and choose 'Page'.");
		} else {
			print " ";
			print "\n</div>";
			return new UnstyledBlock(ob_get_clean());
		}
		print "\n</div>";
		$placeholder = new UnstyledBlock(ob_get_clean());
		
		$title = str_replace('%1', $organizer->getParentComponent()->getDisplayName(),
				_("<em>Sub-Menu of</em> %1 <em>Target Placeholder</em>"));
				
		$controlsHTML = $this->getControlsHTML(
			$organizer,
			$title,
			'', 
			'#F00', '#F99', '#F66');
		$placeholder->setPreHTML($controlsHTML);
		$styleCollection = new StyleCollection(
									'.placeholder_red_outline', 
									'placeholder_red_outline', 
									'Red Outline', 
									'A red outline around a menu placeholder');
		$styleCollection->addSP(new BorderSP('2px', 'solid', '#F00'));
		$placeholder->addStyle($styleCollection);
		return $placeholder;
	}
	
	/**
	 * Answer true if borders and controls should be always visible
	 *
	 * @return boolean
	 * @access protected
	 * @since 12/17/07
	 */
	protected function controlsAlwaysVisible () {
		return true;
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
	function wrapAsDraggable ($component, $id, $class) {
		// Note: Ids are prepended with 'comp_' due to XHTML requiring the Id to start with a letter.
		$component->setPreHTML("<div id='comp_$id' class='$class'>".$component->getPreHTML($null = null));	
		$component->setPostHTML(
			$component->getPostHTML($null = null)
			."
</div>

<script type='text/javascript'>
/* <![CDATA[ */
	
	new Draggable('comp_$id',{revert:true, ghosting:false, handle:'controls_bar_title', scroll: window});
	
	var element = document.get_element_by_id('comp_$id');
	var handle = element.down('.controls_bar_title');
	handle.style.cursor = 'move';
	
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
	function wrapAsDroppable ($component, $id, $allowedDraggables) {
		ArgumentValidator::validate($component, ExtendsValidatorRule::getRule("Component"));
		
		if (!count($allowedDraggables))
			return;
		
		// Note: Ids are prepended with 'comp_' due to XHTML requiring the Id to start with a letter.
		$draggablesArray = "new Array('comp_".implode("', 'comp_", $allowedDraggables)."')";

		$component->setPreHTML("<div id='comp_$id'>".$component->getPreHTML($null = null));
		$dropConfirm = _('Are you sure that you want to move this element here?');
		
		$harmoni = Harmoni::instance();
		$url = str_replace("XXXdraggableXXX", "' + draggableElement.id.replace(/^comp_/, '') + '",
					str_replace("XXXdroppableXXX", "' + droppableElement.id.replace(/^comp_/, '') + '",
						str_replace('&amp;', '&', 
							$harmoni->request->quickURL('ui2', 'moveComponent', 
								array('component' => "XXXdraggableXXX", 
									'destination' => "XXXdroppableXXX",
									'returnNode' => SiteDispatcher::getCurrentNodeId(),
									'returnAction' => $this->_action)))));
		
		$component->setPostHTML(
			$component->getPostHTML($null = null)
			."
</div>

<script type='text/javascript'>
/* <![CDATA[ */
	
	Droppables.add('comp_$id', {
			accept: ".$draggablesArray.",
			hoverclass: 'drop_hover',
			onDrop: function (draggableElement, droppableElement) {
				Draggables.deactivate();
				
// 				if (confirm ('$dropConfirm'))
// 				{					
					Draggables.drags.each(function(draggable) {draggable.options.revert = false;});
					
					droppableElement.style.border ='4px inset #F00';
					
					var moveUrl = '".$url."';
					window.location = moveUrl;
// 				}
			}
		});

/* ]]> */
</script>
");
	}
	
	/**
	 * Answer the form for Adding new components
	 * 
	 * @param string $organizerId
	 * @param integer $cellIndex
	 * @param array $allowed Which components to allow addition of: MenuOrganizer, FlowOrganizer, FixedOrganizer
	 * @return string The form HTML
	 * @access public
	 * @since 4/14/06
	 */
	function getInsertFormHTML ( $director, $organizerId, $cellIndex, $allowed) {
		ob_start();
		$harmoni = Harmoni::instance();
		print "\n<form action='";
		print $harmoni->request->quickURL('ui2', 'addComponent', 
				array('returnNode' => SiteDispatcher::getCurrentNodeId(),
					'returnAction' => $this->_action));
		print "' method='post'>";
		
		print "\n\t<input type='hidden' name='".RequestContext::name('organizerId')."' value='".$organizerId."'/>";
		print "\n\t<input type='hidden' name='".RequestContext::name('cellIndex')."' value='".$cellIndex."'/>";
		
		print "\n\t<div style='text-decoration: underline; cursor: pointer; white-space: nowrap;'";
		print "onclick='this.style.display=\"none\"; this.nextSibling.style.display=\"block\";'";
		print ">";
		print "\n\t\t"._("Insert New...");
		print "\n\t</div>";
		print "<div style='display: none'>";
		
		// Selection of our menu target
		if (in_array('MenuOrganizer', $allowed)) {
			$menuTarget = $this->getDefaultMenuTargetId($director, $organizerId, $cellIndex);
			if (!$menuTarget)
				$menuTarget = 'NewCellInNavOrg';
				
// 			print "<br/>".$menuTarget;
			print "\n\t\t\t<input type='hidden' name='".RequestContext::name('menuTarget')."' value='".$menuTarget."'/>";
		}
		
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
		
		print "\n\t\t<div style='text-align: right;'>";
		print "\n\t\t\t<input type='submit' value='"._('Submit')."'/>";
		print "\n\t\t\t<input type='button' ";
		print "onclick='this.parentNode.parentNode.style.display=\"none\"; this.parentNode.parentNode.previousSibling.style.display=\"block\";'";
		print " value='"._("Cancel")."'/>";
		print "\n\t\t</div>";
		print "\n\t</div>";
		print "</form>";
		return ob_get_clean();
	}
	
	/**
	 * Answer the default menu target or false if one is not available
	 * 
	 * @param object SiteDirector $director
	 * @param string $menuOrganizerId
	 * @param string $menuCellId
	 * @return string OR false if not available
	 * @access public
	 * @since 4/18/06
	 */
	function getDefaultMenuTargetId ( $director, $menuOrganizerId, $menuCellIndex ) {
		$organizer = $director->getSiteComponentById($menuOrganizerId);
		$newTarget = false;
		
		// First, lets target the first empty cell in this organizer 
		// if one is available
		$numCells = $organizer->getTotalNumberOfCells();
			for ($i = 0; $i < $numCells; $i++) {
			if ($i != $menuCellIndex && is_null($organizer->getSubcomponentForCell($i))) {
				$newTarget = $organizer->getId()."_cell:".$i;
				break;
			}
		}
		
		if ($newTarget)
			return $newTarget;
		else
			return $this->getFirstEmptyCellId($organizer->getParentNavOrganizer(), $organizer->getId());
	}
	
	/**
	 * Recursively find the first empty cell in a fixed organizer other
	 * than the one to exclude
	 * 
	 * @param object $organizer The organizer to search below.
	 * @param string $orgIdToExclude
	 * @return string OR false of failure
	 * @access public
	 * @since 4/18/06
	 */
	function getFirstEmptyCellId ( $organizer, $orgIdToExclude ) {
		if ($organizer->getId() != $orgIdToExclude) {
			$numCells = $organizer->getTotalNumberOfCells();
			for ($i = 0; $i < $numCells; $i++) {
				$cellContents = $organizer->getSubcomponentForCell($i);
				if (is_null($cellContents)) {
					return $organizer->getId()."_cell:".$i;
				}
			}
		}
		
		// If we didn't find one in this organizer, search its children
		$childFixedOrganizers = $organizer->getFixedOrganizers();
		foreach (array_keys($childFixedOrganizers) as $key) {
			$result = $this->getFirstEmptyCellId(
							$childFixedOrganizers[$key], 
							$orgIdToExclude);
			if ($result)
				return $result;
		}
		
		return false;
	}
	
}

?>