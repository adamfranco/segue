<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list.act.php,v 1.29 2008/04/10 21:03:13 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");
require_once(MYDIR."/main/modules/window/display.act.php");
require_once(HARMONI."GUIManager/StyleProperties/MinHeightSP.class.php");

require_once(dirname(__FILE__)."/PortalManager.class.php");

/**
 * This is a new portal list that makes use of the new PortalCategories and PortalFolders
 * systems.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list.act.php,v 1.29 2008/04/10 21:03:13 adamfranco Exp $
 */
class listAction
	extends MainWindowAction
{
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Answer the switching form
	 * 
	 * @return string
	 * @access private
	 * @since 3/18/08
	 */
	private function getUiSwitchForm () {
		$displayAction = new displayAction;
		return $displayAction->getUiSwitchForm();
	}
	
	/**
	 * Answer the ui module
	 * 
	 * @return string
	 * @access public
	 * @since 3/18/08
	 */
	public function getUiModule () {
		$displayAction = new displayAction;
		return $displayAction->getUiModule();
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		ob_start();
		
		// print the ui-mode changing form
		print $this->getUiSwitchForm();
	
		$authN = Services::getService("AuthN");
		if ($authN->isUserAuthenticatedWithAnyType()) {
			print _("Your Portal");
		} else {
			print _("Portal (log in to see your own portal)");
		}
		print "\n\t<div style='clear: both; height: 0px;'>&nbsp;</div>";
		
		return ob_get_clean();
	}
	
	/**
	 * Execute this action
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/1/08
	 */
	public function buildContent () {
		$actionRows = $this->getActionRows();
		$portalWrapper = $actionRows->add(new Container(new XLayout, BLANK, 1), "100%", null, CENTER, TOP);
		
		$this->addHeadJs();
		
		$harmoni = Harmoni::instance();
		// Categories
		ob_start();
		$portalMgr = PortalManager::instance();
		print "\n<ul class='portal_categories'>";
		foreach ($portalMgr->getCategories() as $category) {
			print "\n\t<li class='portal_category'>";
			print "\n\t\t<div class='title'>".$category->getDisplayName()."</div>";
			if (strlen($category->getDescription()))
				print "\n\t\t<div class='description'>".$category->getDescription()."</div>";
			
			print "\n\t\t<ul class='portal_folders'>";
			foreach ($category->getFolders() as $folder) {
				print "\n\t\t\t<li class='portal_folder";
				if ($folder->getIdString() == $this->getCurrentFolderId())
					print " current";
				print "'>";
				print "\n\t\t\t\t<div class='title'>";
				print "<a href='";
				print $harmoni->request->quickURL(
					$harmoni->request->getRequestedModule(),
					$harmoni->request->getRequestedAction(),
					array('folder' => $folder->getIdString()));
				print "'>";
				print $folder->getDisplayName();
				print "</a></div>";
				if (strlen($folder->getDescription()))
					print "\n\t\t\t\t<div class='description'>".$folder->getDescription()."</div>";
				print "\n\t\t\t</li>";
			}
			print "\n\t\t</ul>";
			
			print "\n\t</li>";
		}
		print "\n</ul>";
		$portalWrapper->add(new Block(ob_get_clean(), STANDARD_BLOCK), "150px", null, CENTER, TOP);
		
		/*********************************************************
		 * Sites in the current folder.
		 *********************************************************/
		$siteList = $portalWrapper->add(new Container(new YLayout, BLANK, 1), null, null, CENTER, TOP);
		$currentFolder = $portalMgr->getFolder($this->getCurrentFolderId());
		
		// controls
		$controls = $currentFolder->getControlsHtml();
		if (strlen($controls))
			$siteList->add(new Heading($controls, 3));
		
		// Sites
		$slots = $currentFolder->getSlots();
		$resultPrinter = new ArrayResultPrinter($slots, 1, 20, array($this, "printSlot"));
		$this->showEditControls = $currentFolder->showEditControls();
		if ($currentFolder->slotsAreFilteredByAuthorization())
			$resultLayout = $resultPrinter->getLayout();
		else
			$resultLayout = $resultPrinter->getLayout(array($this, "canView"));
		
		if ($resultPrinter->getNumItemsPrinted())
			$siteList->add($resultLayout, "100%", null, LEFT, CENTER);
		else
			$siteList->add(new Block(_('No items to display.'), STANDARD_BLOCK), "100%", null, LEFT, CENTER);
		
	}
	
	/**
	 * Answer a list of categories
	 * 
	 * @return array of PortalCategory objects
	 * @access protected
	 * @since 4/1/08
	 */
	protected function getCategories () {
		return array(new MainPortalCategory);
	}
	
	/**
	 * Add Javascript function to our header
	 * 
	 * @return void
	 * @access protected
	 * @since 7/25/08
	 */
	protected function addHeadJs () {
		$harmoni = Harmoni::instance();
		ob_start();
		
		try {
			$selectedSlot = $this->getSelectedSlot();
			$selectedSlotName = $selectedSlot->getShortname();
		} catch (OperationFailedException $e) {
			$selectedSlotname = '';
		}
		
		print "\n
		<script type='text/javascript'>
		// <![CDATA[
		
		/**
		 * Portal is a static class for namespacing portal-related functions
		 *
		 * @access public
		 * @since 7/25/08
		 */
		function Portal () {
			var selectedSlotname = '$selectedSlotname';
		}
		
		/**
		 * Set the selected slotname and sent an asynchronous request to set
		 * the slotname in the session.
		 * 
		 * @param string slotName
		 * @return void
		 * @access public
		 * @since 7/25/08
		 */
		Portal.setSelectedSlotname = function (slotName) {
			Portal.selectedSlotname = slotName;
			
			// Send off an asynchronous request to record the selected slotname
			// for future page-loads
			// @todo
		}
		
		/**
		 * Select a site for copying.
		 *
		 * This will set up the placeholders on the current page as destinations
		 * for the copied site as well as dish off an asynchronous request to 
		 * set a session variable for the selected site so that future page loads
		 * will have the site selected.
		 * 
		 * @param string slotName	The slot name to copy
		 * @param string siteTitle	The title of the site selected
		 * @param DOMElement link	The link clicked
		 * @return void
		 * @access public
		 * @since 7/25/08
		 */
		Portal.selectForCopy = function (slotName, siteTitle, link) {
			// Cancel all other selections
			var selectLinks = document.getElementsByClassName('portal_slot_select_link');
			for (var i = 0; i < selectLinks.length; i++) {
				var selectLink = selectLinks[i];
				if (selectLink.innerHTML == '"._('cancel copy')."') {
					selectLink.onclick();
				}
			}
			
			// Add 'paste' links to all of the placeholders 
			var copyAreas = document.getElementsByClassName('portal_slot_copy_area');
			for (var i = 0; i < copyAreas.length; i++) {
				var area = copyAreas[i];
				area.style.display = 'inline';
				
				var copyLink = area.getElementsByTagName('a').item(0);
				var message = \""._("copy '%1' here...")."\";
				copyLink.innerHTML = message.replace(/%1/, siteTitle);
			}
			
			// Set the selected slotname property, then send off an asynchronous 
			// request to record the selected slotname for future page-loads
			Portal.setSelectedSlotname(slotName);
			
			// Change the link to a cancel select link
			link.innerHTML = '"._('cancel copy')."';
			link.onclick = function () {
				Portal.deselectForCopy(slotName, siteTitle, link);
			}
		}
		
		/**
		 * Deselect the current site for copying
		 *
		 * 
		 * 
		 * @param string slotName	The slot name to copy
		 * @param string siteTitle	The title of the site selected
		 * @param DOMElement link	The link clicked
		 * @access public
		 * @since 7/25/08
		 */
		Portal.deselectForCopy = function (slotName, siteTitle, link) {
			// Remove 'paste' links to all of the placeholders 
			var copyAreas = document.getElementsByClassName('portal_slot_copy_area');
			for (var i = 0; i < copyAreas.length; i++) {
				var area = copyAreas[i];
				area.style.display = 'none';
			}
			
			// Send off an asynchronous request to record the deselection of the slotname
			// for future page-loads
			
			
			// Change the link to a select select link
			link.innerHTML = '"._('select for copy')."';
			link.onclick = function () {
				Portal.selectForCopy(slotName, siteTitle, link);
			}
		}
		
		// ]]>
		</script>
		";
		
		$handler = $harmoni->getOutputHandler();
		$handler->setHead($handler->getHead().ob_get_clean());
	}
	
	/**
	 * @var object Slot $selectedSlot;  
	 * @access private
	 * @since 7/25/08
	 */
	private $selectedSlot;
	
	/**
	 * @var string $selectedSiteTitle;  
	 * @access private
	 * @since 7/25/08
	 */
	private $selectedSiteTitle;
	
	/**
	 * Answer the selected slot or throw an OperationFailedException if none exists.
	 * 
	 * @return object Slot
	 * @access protected
	 * @since 7/25/08
	 */
	protected function getSelectedSlot () {
		if (!isset($this->selectedSlot)) {
			if (!isset($_SESSION['portal_slot_selection']) || $_SESSION['portal_slot_selection'])
				throw new OperationFailedException("No placeholder selected.");
			
			$slotMgr = SlotManager::instance();
			$this->selectedSlot = $slotMgr->getSlotByShortname($_SESSION['portal_slot_selection']);
		}
		return $this->selectedSlot;
	}
	
	/**
	 * Answer the selected slot title  or throw an OperationFailedException if none exists.
	 * 
	 * @return string
	 * @access protected
	 * @since 7/25/08
	 */
	protected function getSelectedSiteTitle () {
		if (!isset($this->selectedSiteTitle)) {
			$slot = $this->getSelectedSlot();
			$siteAsset = $slot->getSiteAsset();
			$this->selectedSiteTitle = $siteAsset->getDisplayName();
		}
		return $this->selectedSiteTitle;
	}
	
	/**
	 * Answer the current Folder id
	 * 
	 * @return string
	 * @access private
	 * @since 4/1/08
	 */
	private function getCurrentFolderId () {
		if (!isset($this->currentFolderId)) {
			if (RequestContext::value('folder')) {
				try {
					$portalMgr = PortalManager::instance();
					$folder = $portalMgr->getFolder(RequestContext::value('folder'));
					$this->currentFolderId = $folder->getIdString();
				} catch (UnknownIdException $e) {
					$this->currentFolderId = 'personal';
				}
			} else {
				$this->currentFolderId = 'personal';
			}
		}
		
		return $this->currentFolderId;
	}
	
	/**
	 * Print out a slot
	 * 
	 * @param object Slot $slot
	 * @return void
	 * @access protected
	 * @since 8/22/07
	 */
	public function printSlot ( Slot $slot ) {
		if ($slot->getSiteId()) {
			try {
				return $this->printSiteShort($slot->getSiteAsset(), $this, 0);
			} 
			// Cached slot may not know that it's site was deleted.
			catch(UnknownIdException $e) {
			}
		} 
		// If no site is created

		ob_start();
		print $slot->getShortname();
		print " - ";
		if ($slot->isUserOwner()) {
			$harmoni = Harmoni::instance();
			print " <a href='".$harmoni->request->quickURL($this->getUiModule(), 'add', array('slot' => $slot->getShortname()))."' class='create_site_link'>"._("Create Site")."</a>";
			
			$authN = Services::getService("AuthN");
			try {
				$personalShortname = PersonalSlot::getPersonalShortname($authN->getFirstUserId());
			} catch (OperationFailedException $e) {
				$personalShortname = null;
			}
			if ($slot->getType() == Slot::personal &&
				$slot->getShortName() != $personalShortname) 
			{
				$harmoni = Harmoni::instance();
				$harmoni->request->startNamespace("slots");
				print " | <a href='";
				print $harmoni->request->quickURL('slots', 'delete', 
					array('name' => $slot->getShortName(), 
						'returnModule' => $harmoni->request->getRequestedModule(),
						'returnAction' => $harmoni->request->getRequestedAction()));
				print "'";
				print " onclick=\"return confirm('"._("Are you sure that you want to delete this placeholder?")."');\" ";
				print ">"._("delete placeholder")."</a>";
				$harmoni->request->endNamespace();
			}
			
			try {
				$selectedTitle = HtmlString::getSafeHtml($this->getSelectedSiteTitle());
			} catch (OperationFailedException $e) {
				$selectedTitle = '';
			}
			print "<span class='portal_slot_copy_area' style='display: none'> | <a href='#' class='portal_slot_copy_link' onclick=\"Portal.copyToSlot('".$slot->getShortname()."', this)\">".$selectedTitle."</a></span>";
		} else {
			print " <span class='site_not_created_message'>"._("No Site Created")."</span>";
		}
		return new Block(ob_get_clean(), STANDARD_BLOCK);
	}
	
	/**
	 * return true if the current user can view this asset
	 * 
	 * @param object Slot $slot
	 * @return boolean
	 * @access public
	 * @since 1/18/06
	 */
	public function canView( Slot $slot ) {		
		if (!$slot->siteExists())
			return true;
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		try {
			if ($authZ->isUserAuthorizedBelow($idManager->getId("edu.middlebury.authorization.view"), $slot->getSiteId()))
			{
				return TRUE;
			} else {
				return FALSE;
			}
		} catch (UnknownIdException $e)  {
			return true;
		}
	}
	
	/**
	 * 
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 1/18/06
	 */
	public function printSiteShort(Asset $asset, $action, $num) {
		$harmoni = Harmoni::instance();
		$assetId = $asset->getId();
		
		$authZ = Services::getService('AuthZ');
		$idMgr = Services::getService('Id');
		
		if (!$authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view'), $assetId))
			return new UnstyledBlock('', BLANK);
						
		$container = new Container(new YLayout, BLOCK, STANDARD_BLOCK);
		$fillContainerSC = new StyleCollection("*.fillcontainer", "fillcontainer", "Fill Container", "Elements with this style will fill their container.");
		$fillContainerSC->addSP(new MinHeightSP("88%"));
	// 	$fillContainerSC->addSP(new WidthSP("100%"));
	// 	$fillContainerSC->addSP(new BorderSP("3px", "solid", "#F00"));
		$container->addStyle($fillContainerSC);
		
		$centered = new StyleCollection("*.centered", "centered", "Centered", "Centered Text");
		$centered->addSP(new TextAlignSP("center"));	
		
		// Use the alias instead of the Id if it is available.
		$slotManager = SlotManager::instance();
		try {
			$slot = $slotManager->getSlotBySiteId($assetId);
			$params = array('site' => $slot->getShortname());
		} catch (Exception $e) {
			$params = array('node' => $assetId->getIdString());
		}
		$viewUrl = $harmoni->request->quickURL('view', 'html', $params);
		
		// Print out the content
		ob_start();
		print "\n\t<div class='portal_list_slotname'>";
		if (isset($slot)) {
			print $slot->getShortname();
		} else {
			print _("ID#").": ".$assetId->getIdString();
		}
		print "\n\t</div>";
		print "\n\t<div class='portal_list_site_title'>";
		print "\n\t\t<a href='".$viewUrl."'>";
		print "\n\t\t\t<strong>".HtmlString::getSafeHtml($asset->getDisplayName())."</strong>";
		print "\n\t\t</a>";
		print "\n\t</div>";
		
		print "\n\t<div class='portal_list_controls'>\n\t\t";
		$controls = array();
		
		$controls[] = "<a href='".$viewUrl."'>"._("view")."</a>";
		
		if ($this->showEditControls) {
			if (
			// While it is more correct to check modify permission as well, people with
			// the editor role (and hence modify permission) should also be able to 
			// add_children. Only checking one of these currently cuts the number of 
			// AZ queries in half.
			// 	$authZ->isUserAuthorizedBelow($idMgr->getId('edu.middlebury.authorization.modify'), $assetId) ||
				$authZ->isUserAuthorizedBelow($idMgr->getId('edu.middlebury.authorization.add_children'), $assetId)) 
			{
				$controls[] = "<a href='".$harmoni->request->quickURL($action->getUiModule(), 'editview', array('node' => $assetId->getIdString()))."'>"._("edit")."</a>";
			
				if ($action->getUiModule() == 'ui2') {
					$controls[] = "<a href='".$harmoni->request->quickURL($action->getUiModule(), 'arrangeview', array('node' => $assetId->getIdString()))."'>"._("arrange")."</a>";
				}
				
				if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.delete'), $assetId))
					$controls[] = "<a href='".$harmoni->request->quickURL($action->getUiModule(), 'deleteComponent', array('node' => $assetId->getIdString()))."' onclick=\"if (!confirm('"._("Are you sure that you want to permenantly delete this site?")."')) { return false; }\">"._("delete")."</a>";
				
				
				// Add a control to select this site for copying. This should probably
				// have its own authorization, but we'll use add_children/modify for now.
				if (isset($_SESSION['portal_slot_selection']) && $_SESSION['portal_slot_selection']) {
					$controls[] = "<a href='#' onclick=\"Portal.deselectForCopy('".$assetId->getIdString()."', '".addslashes(str_replace('"', '&quot;', HtmlString::getSafeHtml($asset->getDisplayName())))."', this);\" class='portal_slot_select_link'>"._("cancel copy")."</a>";
				} else {
					$controls[] = "<a href='#' onclick=\"Portal.selectForCopy('".$assetId->getIdString()."', '".addslashes(str_replace('"', '&quot;', HtmlString::getSafeHtml($asset->getDisplayName())))."', this);\" class='portal_slot_select_link'>"._("select for copy")."</a>";
				}			
			}
		}
		
		print implode("\n\t\t | ", $controls);
		print "\n\t</div>";
		
		$description = HtmlString::withValue($asset->getDescription());
		$description->trim(25);
		print  "\n\t<div class='portal_list_site_description'>".$description->asString()."</div>";	
		print "\n\t<div style='clear: both;'></div>";
		
		$component = new UnstyledBlock(ob_get_clean());
		$container->add($component, "100%", null, LEFT, TOP);
		
		return $container;
	}
}

?>