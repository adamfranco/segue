<?php
/**
 * @since 4/6/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ArrangeModeSiteVisitor.class.php,v 1.12 2007/11/09 22:57:41 adamfranco Exp $
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
 * @version $Id: ArrangeModeSiteVisitor.class.php,v 1.12 2007/11/09 22:57:41 adamfranco Exp $
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
		// enter links in our head to load needed javascript libraries
		$harmoni = Harmoni::instance();
		$outputHandler = $harmoni->getOutputHandler();
		$outputHandler->setHead(
			$outputHandler->getHead()
			."
		<script src='".MYPATH."/javascript/scriptaculous-js/lib/prototype.js' type='text/javascript'></script>
		<script src='".MYPATH."/javascript/scriptaculous-js/src/scriptaculous.js' type='text/javascript'></script>

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
		$childOrganizer = $siteNavBlock->getOrganizer();
		$childGuiComponent = $childOrganizer->acceptVisitor($this);
		
		// Check completeness and render any nodes still waiting for targets
		foreach (array_keys($this->_missingTargets) as $targetId) {			
			if (isset($this->_missingTargetWidths[$targetId]) && $this->_missingTargetWidths[$targetId])
				$width = $this->_missingTargetWidths[$targetId];
			else
				$width = null;
			
			$this->_emptyCellContainers[$targetId]->insertAtPlaceholder(
				$this->_emptyCellPlaceholders[$targetId],
				$this->_missingTargets[$targetId], 
				$width, '100%', null, TOP);
				
			unset($this->_emptyCellContainers[$targetId]);
			unset($this->_emptyCellPlaceholders[$targetId]);
			unset($this->_missingTargets[$targetId]);
			unset($this->_missingTargetWidths[$targetId]);
		}
		
		
		
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
		
		// returning the entire site in GUI component object tree.
// 		printpre($this);
// 		print "<hr/>";
// 		printpre($siteNavBlock->_director->_activeNodes);
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
		$primaryColor = '#090';
		$secondaryColor = '#9F9';
		$halfLineWidth = 1;
		$lineWidth = ($halfLineWidth * 2).'px'; $halfLineWidth = $halfLineWidth.'px';
		
		// Get our content and heading objects from the parent visitor
		$content = $guiContainer->getComponent(2);
		if ($content)
			$heading = $guiContainer->getComponent(1);
		else {
			$heading = null;
			$content = $guiContainer->getComponent(1);
		}
		
		
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$block->getQualifierId()))
		{
			$controlsHTML = $this->getControlsHTML(
				"<em>".$this->_classNames['Block']."</em>", 
				$block->acceptVisitor($this->_controlsVisitor), 
				'#090', '#9F9', '#6C6');
			$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
			
			if ($block->showDisplayName()) {
				$styleCollection = new StyleCollection(
											'.block_side_outline', 
											'block_side_outline', 
											'Side Outline', 
											'A side outline around block titles');
				$styleCollection->addSP(new BorderTopSP($lineWidth, 'solid', $primaryColor));
				$styleCollection->addSP(new BorderLeftSP($lineWidth, 'solid', $primaryColor));
				$styleCollection->addSP(new BorderRightSP($lineWidth, 'solid', $primaryColor));
				
				$heading->addStyle($styleCollection);
			}
			
			$styleCollection = new StyleCollection(
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
		return $harmoni->request->quickURL(
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
			$controlsHTML = $this->getControlsHTML(
				"<em>".$this->_classNames['Block']."</em>", 
				$block->acceptVisitor($this->_controlsVisitor), 
				'#090', '#9F9', '#6C6');
			$guiContainer->setPreHTML($controlsHTML);
			
			$styleCollection = new StyleCollection(
										'.nav_outline', 
										'nav_outline', 
										'Side Outline', 
										'A side outline around block titles');
			$styleCollection->addSP(new BorderSP('2px', 'solid', '#090'));
			$guiContainer->addStyle($styleCollection);
			
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
			$controlsHTML = $this->getControlsHTML(
				"<em>".$this->_classNames['NavBlock']."</em>", 
				$navBlock->acceptVisitor($this->_controlsVisitor), 
				'#090', '#9F9', '#6C6');
			$guiContainer->setPreHTML($controlsHTML);
			
			$styleCollection = new StyleCollection(
										'.nav_outline', 
										'nav_outline', 
										'Side Outline', 
										'A side outline around block titles');
			$styleCollection->addSP(new BorderSP('2px', 'solid', '#090'));
			$guiContainer->addStyle($styleCollection);
			
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
			$controlsHTML = $this->getControlsHTML(
				$organizer->getDisplayName(), 
				$organizer->acceptVisitor($this->_controlsVisitor),
				'#F00', '#F99', '#F66');
			$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
			
			$styleCollection = new StyleCollection(
										'.org_red_outline', 
										'org_red_outline', 
										'Red Outline', 
										'A red outline around organizers');
			$styleCollection->addSP(new BorderSP('1px', 'solid', '#F00'));
			$styleCollection->addSP(new HeightSP('100%'));
			$guiContainer->addStyle($styleCollection);
			
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
	 * Visit a flow organizer and return the resultant GUI component [a container].
	 * 
	 * @param object FlowOrganizerSiteComponent
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	public function visitFlowOrganizer ( FlowOrganizerSiteComponent $organizer ) {
		$numCells = $organizer->getTotalNumberOfCells();
		
		if ($organizer->getNumRows() == 0)
			$cellsPerPage = $numCells + 1;
		// If we are limiting to a number of rows, we are paginating.
		else
			$cellsPerPage = $organizer->getNumColumns() * $organizer->getNumRows();
		
		$childGuiComponents = array();
		for ($i = 0; $i < $numCells; $i++) {
			$child = $organizer->getSubcomponentForCell($i);
			$childGuiComponent = $child->acceptVisitor($this);
			if ($childGuiComponent) {
				$this->wrapAsDroppable($childGuiComponent, 
					$organizer->getId()."_cell:".$i,
					array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
				$childGuiComponents[] = $childGuiComponent;
			}
		}
		
		// Controls and organizer dragging.
		// Add controls bar and border
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"), 
			$organizer->getQualifierId()))
		{
			$pluginManager = Services::getService("PluginManager");
			$addBlock = new UnstyledBlock($this->getAddFormHTML($organizer->getId(), $i, $pluginManager->getEnabledPlugins()));
			$this->wrapAsDroppable($addBlock, 
					$organizer->getId()."_cell:".$i,
					array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
			$childGuiComponents[] = $addBlock;
		}
		
		
		$resultPrinter = new ArrayResultPrinter($childGuiComponents,
									$organizer->getNumColumns(), $cellsPerPage);
		$resultPrinter->setRenderDirection($organizer->getDirection());
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$organizer->getQualifierId()))
		{
			$resultPrinter->setTdStyles("border: 1px solid #00F; padding: 6px;");
			$resultPrinter->addLinksStyleProperty(new BorderSP("1px", "solid", "#00F"));
		}
		$resultPrinter->setNamespace('pages_'.$organizer->getId());
		
		$guiContainer = $resultPrinter->getLayout();
		
		
		// Controls and organizer dragging.
		// Add controls bar and border
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$organizer->getQualifierId()))
		{
			$controlsHTML = $this->getControlsHTML(
				$organizer->getDisplayName()
				.'',
				$organizer->acceptVisitor($this->_controlsVisitor),
				'#00F', '#99F', '#66F');
			$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
					
			$styleCollection = new StyleCollection(
										'.org_blue_outline', 
										'org_blue_outline', 
										'Blue Outline', 
										'A blue outline around organizers');
			$styleCollection->addSP(new BorderSP('1px', 'solid', '#00F'));
			$guiContainer->addStyle($styleCollection);
			
			if (count($organizer->getVisibleDestinationsForPossibleAddition()))
				$this->wrapAsDraggable($guiContainer, $organizer->getId(), 'FlowOrganizer');
		}
		
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
	public function visitMenuOrganizer ( MenuOrganizerSiteComponent $organizer ) {	
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		// Choose layout direction based on number of rows
		if ($this->_menuNestingLevel) {
			$layout = new YLayout();
		} else if ($organizer->getDirection() == "Left-Right/Top-Bottom") {
			$layout = new XLayout();
		} else if ($organizer->getDirection() == "Right-Left/Top-Bottom") {
			$layout = new XLayout();
			$layout->setRenderDirection("Right-Left/Top-Bottom");
		} else {
			$layout = new YLayout();
		}
		
		if ($this->_menuNestingLevel)
			$guiContainer = new SubMenu ( $layout, $this->_menuNestingLevel);
		else
			$guiContainer = new Menu ( $layout, 1);
		$currentNestingLevel = $this->_menuNestingLevel;
		
		$numCells = $organizer->getTotalNumberOfCells();		
		if ($numCells) {
			for ($i = 0; $i < $numCells; $i++) {
				$child = $organizer->getSubcomponentForCell($i);
				$childGuiComponents = $child->acceptVisitor($this, true);
				
				// If we have multiple Child gui components, then we are dealing
				// with a menu item followed by a sub menu
				if ($childGuiComponents === false || (is_array($childGuiComponents) && !count($childGuiComponents))) {
					// do nothing
				} else if (is_array($childGuiComponents)) {
					// wrap the menu item as droppable
					$this->wrapAsDroppable($childGuiComponents[0], 
						$organizer->getId()."_cell:".$i,
						array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
					
					// Add each of the the menuItems/submenus
					foreach (array_keys($childGuiComponents) as $key) {
						$guiContainer->add($childGuiComponents[$key]);
					}
				} else {
					$guiContainer->add($childGuiComponents);
					$this->wrapAsDroppable($childGuiComponents, 
						$organizer->getId()."_cell:".$i,
						array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
				}
			}
			
			$i++;
		} else if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"), 
			$organizer->getQualifierId()))
		{
			// Add a placeholder to our target if we don't have any children
			$placeholder = new UnstyledBlock("<div style='height: 50px;'>&nbsp;</div>");
			
			$controlsHTML = $this->getControlsHTML(
				$organizer->getDisplayName()." <em>"._("Target Placeholder")."</em>",
				'', 
				'#F00', '#F99', '#F66');
			$placeholder->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
			$styleCollection = new StyleCollection(
										'.placeholder_red_outline', 
										'placeholder_red_outline', 
										'Red Outline', 
										'A red outline around a menu placeholder');
			$styleCollection->addSP(new BorderSP('2px', 'solid', '#F00'));
			$placeholder->addStyle($styleCollection);
			
			$this->_missingTargets[$organizer->getTargetId()] = $placeholder;
			
			$i=0;
		}
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.add_children"), 
			$organizer->getQualifierId()))
		{
			$allowed = array();
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'ContentPage_multipart');
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SidebarContentPage_multipart');
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SubMenu_multipart');
			$allowed[] = new Type('segue-multipart', 'edu.middlebury', 'SidebarSubMenu_multipart');
			$allowed[] = new Type('segue', 'edu.middlebury', 'NavBlock');
			$pluginManager = Services::getService("PluginManager");
			$allowed = array_merge($allowed, $pluginManager->getEnabledPlugins());
			
			$childComponent = $guiContainer->add(new MenuItem($this->getAddFormHTML($organizer->getId(), $i, $allowed), 2), null, '100%', null, TOP);
			$this->wrapAsDroppable($childComponent, 
					$organizer->getId()."_cell:".$i,
					array_keys($organizer->getVisibleComponentsForPossibleAdditionToCell($i)));
		}
		
		if ($authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.modify"), 
			$organizer->getQualifierId()))
		{
			$controlsHTML = $this->getControlsHTML(
				$organizer->getDisplayName(),
				$organizer->acceptVisitor($this->_controlsVisitor), 
				'#00F', '#99F', '#66F', $currentNestingLevel);
			$guiContainer->setPreHTML($controlsHTML.$guiContainer->getPreHTML($null = null));
			
			$styleCollection = new StyleCollection(
										'.menu_blue_outline', 
										'menu_blue_outline', 
										'Blue Outline', 
										'A blue outline around organizers');
			$styleCollection->addSP(new BorderSP('2px', 'solid', '#00F'));
			$guiContainer->addStyle($styleCollection);
			
			if (count($organizer->getVisibleDestinationsForPossibleAddition()))
				$this->wrapAsDraggable($guiContainer, $organizer->getId(), 'FlowOrganizer');
		}
		
		return $guiContainer;
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
		$component->setPreHTML("<div id='$id' class='$class'>".$component->getPreHTML($null = null));	
		$component->setPostHTML(
			$component->getPostHTML($null = null)
			."
</div>

<script type='text/javascript'>
/* <![CDATA[ */
	
	new Draggable('$id',{revert:true, ghosting:false});

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
		
		$draggablesArray = "new Array('".implode("', '", $allowedDraggables)."')";

		$component->setPreHTML("<div id='$id'>".$component->getPreHTML($null = null));
		$dropConfirm = _('Are you sure that you want to move this element here?');
		
		$harmoni = Harmoni::instance();
		$url = str_replace("XXXdraggableXXX", "' + draggableElement.id + '",
					str_replace("XXXdroppableXXX", "' + droppableElement.id + '",
						str_replace('&amp;', '&', 
							$harmoni->request->quickURL('ui2', 'moveComponent', 
								array('component' => "XXXdraggableXXX", 
									'destination' => "XXXdroppableXXX",
									'returnNode' => RequestContext::value('node'),
									'returnAction' => $this->_action)))));
		
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
				Draggables.deactivate();
				
				if (confirm ('$dropConfirm'))
				{					
					Draggables.drags.each(function(draggable) {draggable.options.revert = false;});
					
					droppableElement.style.border ='4px inset #F00';
					
					var moveUrl = '".$url."';
					window.location = moveUrl;
				}
			}
		});

/* ]]> */
</script>
");
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
		ob_start();

		print "\n<div style='"
			."color: #000; "
			."background-color: $backgroundColor; "
			."border-top: $lineWidth solid $borderColor; "
			."border-left: $lineWidth solid $borderColor; "
			."border-right: $lineWidth solid $borderColor; "
			.(($leftIndentLevel)?"margin-left: 10px; ":"")
			."'"
			." onmouseover='showControlsLink(this)'"
			." onmouseout='hideControlsLink(this)'>";
		print "\n<table border='0' cellpadding='0' cellspacing='0'"
			." style='width: 100%; padding: 0px; margin: 0px; cursor: move;'"
// 			." onmousemove='if(which == 1) { alert(\"dragging\"); drag(this.parentNode, this, screenX, screenY); }'"
// 			." onmouseup='endDrag(this.parentNode)'"
			.">";
		print "\n\t<tr>";
		print "\n\t\t<td style='white-space: nowrap;'>";
		print "\n\t\t".$title;
		print "\n\t\t</td>";
		print "\n\t\t<td style='text-align: right;'>";
		print "\n\t\t\t\t<span class='controls_link'"
			."style='visibility: hidden; cursor: pointer; white-space: nowrap;'"
			." onclick='toggleControls(this.parentNode.parentNode.parentNode.parentNode.parentNode);'>";
		print "\n\t\t\t"._("Options");
		print "\n\t\t\t</span>";
		print "\n\t\t</td>";
		print "\n\t</tr>";
		print "\n</table>";
		
		print "\n\t\t\t<div class='controls' style='display: none; border-top: 1px solid $dividerColor;'>";
		print $controlsHTML;
		print "\n\t\t\t\t</div>";
		
// 		if (!$float) {
// 			print "\n<div style='display: block;' class='controls_spacer'>&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;</div>";
// 		}
		
		print "\n</div>";
		
		return ob_get_clean();
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
				array('returnNode' => RequestContext::value('node'),
					'returnAction' => $this->_action));
		print "' method='post'>";
		
		print "\n\t<input type='hidden' name='".RequestContext::name('organizerId')."' value='".$organizerId."'/>";
		print "\n\t<input type='hidden' name='".RequestContext::name('cellIndex')."' value='".$cellIndex."'/>";
		
		print "\n\t<div style='text-decoration: underline; cursor: pointer; white-space: nowrap;'";
		print "onclick='this.style.display=\"none\"; this.nextSibling.nextSibling.style.display=\"block\";'";
		print ">";
		print "\n\t\t"._("Insert New...");
		print "\n\t</div>";
		print "\n\t\t<div style='display: none'>";
		
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
		
		print "\n\t\t\t<div style='text-align: right;'>";
		print "\n\t\t\t\t<input type='submit' value='"._('Submit')."'/>";
		print "\n\t\t\t\t<input type='button' ";
		print "onclick='this.parentNode.parentNode.style.display=\"none\"; this.parentNode.parentNode.previousSibling.previousSibling.style.display=\"block\";'";
		print " value='"._("Cancel")."'/>";
		print "\n\t\t\t</div>";
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