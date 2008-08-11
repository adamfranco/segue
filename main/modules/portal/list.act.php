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
			$selectedSlotname = $selectedSlot->getShortname();
			$siteAsset = $selectedSlot->getSiteAsset();
			$selectedSiteId = $siteAsset->getId()->getIdString();
			$selectedSiteTitle = addslashes(HtmlString::getSafeHtml($siteAsset->getDisplayName()));
		} catch (OperationFailedException $e) {
			$selectedSlotname = '';
			$selectedSiteId = '';
			$selectedSiteTitle = '';
		}
		
		print "\n
		
		<script type='text/javascript' src='".MYPATH."/javascript/SiteCopyPanel.js'></script>
		<script type='text/javascript' src='".POLYPHONY_PATH."javascript/CenteredPanel.js'></script>
		<script type='text/javascript' src='".MYPATH."/javascript/scriptaculous-js/lib/prototype.js'></script>
		
		<script type='text/javascript'>
		// <![CDATA[
		
		/**
		 * Portal is a static class for namespacing portal-related functions
		 *
		 * @access public
		 * @since 7/25/08
		 */
		function Portal () {
		}
		
		Portal.selectedSlotname = '".$selectedSlotname."';
		Portal.selectedSiteId = '".$selectedSiteId."';
		Portal.selectedSiteTitle = '".$selectedSiteTitle."';
		
		/**
		 * Set the selected slotname and sent an asynchronous request to set
		 * the slotname in the session.
		 * 
		 * @param string slotName
		 * @param string siteTitle	The title of the site selected
		 * @param DOMElement link	The link clicked
		 * @return void
		 * @access public
		 * @since 7/25/08
		 */
		Portal.setSelectedSlotname = function (slotName, siteId, siteTitle, link) {
			Portal.selectedSlotname = slotName;
			Portal.selectedSiteId = siteId;
			Portal.selectedSiteTitle = siteTitle;
			
			// Send off an asynchronous request to record the selected slotname
			// for future page-loads
			var url = Harmoni.quickUrl('portal', 'select_for_copy', {'slot': slotName});
			var req = Harmoni.createRequest();
			if (req) {
				
				// Set a callback for displaying errors.
				req.onreadystatechange = function () {
					// only if req shows 'loaded'
					if (req.readyState == 4) {
						// only if we get a good load should we continue.
						if (req.status == 200 && req.responseXML) {
	// 						alert(req.responseText);
							var errors = req.responseXML.getElementsByTagName('error');
							if (errors.length) {
								Portal.deselectForCopy(slotName, siteTitle, link);
								
								var error = errors[0];
								alert(error.getAttribute('type') + ': ' + error.firstChild.nodeValue);
								
							}
						} else {
							alert(\"There was a problem retrieving the XML data:\\n\" +
								req.statusText);
						}
					}
				} 
			
				req.open('GET', url, true);
				req.send(null);
			} else {
				alert(\"Error: Unable to execute AJAX request. \\nPlease upgrade your browser.\");
			}	
			
		}
		
		/**
		 * Unset the selected slotname and sent an asynchronous request to unset
		 * the slotname in the session.
		 * 
		 * @return void
		 * @access public
		 * @since 7/25/08
		 */
		Portal.unsetSelectedSlotname = function () {
			delete Portal.selectedSlotname;
			delete Portal.selectedSiteId;
			delete Portal.selectedSiteTitle;
			
			// Send off an asynchronous request to record the selected slotname
			// for future page-loads
			var url = Harmoni.quickUrl('portal', 'deselect_for_copy');
			var req = Harmoni.createRequest();
			if (req) {
			
				// Set a callback for displaying errors.
				req.onreadystatechange = function () {
					// only if req shows 'loaded'
					if (req.readyState == 4) {
						// only if we get a good load should we continue.
						if (req.status == 200 && req.responseXML) {
	// 						alert(req.responseText);
							var errors = req.responseXML.getElementsByTagName('error');
							if (errors.length) {
								var error = errors[0];
								alert(error.getAttribute('type') + ': ' + error.firstChild.nodeValue);
							}
						} else {
							alert(\"There was a problem retrieving the XML data:\\n\" +
								req.statusText);
						}
					}
				} 
			
				req.open('GET', url, true);
				req.send(null);
			} else {
				alert(\"Error: Unable to execute AJAX request. \\nPlease upgrade your browser.\");
			}	
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
		 * @param string siteId		The site id selected
		 * @param string siteTitle	The title of the site selected
		 * @param DOMElement link	The link clicked
		 * @return void
		 * @access public
		 * @since 7/25/08
		 */
		Portal.selectForCopy = function (slotName, siteId, siteTitle, link) {
			// Set the selected slotname property, then send off an asynchronous 
			// request to record the selected slotname for future page-loads
			Portal.setSelectedSlotname(slotName, siteId, siteTitle, link);
			
			
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
			
			
			
			// Change the link to a cancel select link
			link.innerHTML = '"._('cancel copy')."';
			link.onclick = function () {
				Portal.deselectForCopy(slotName, siteId, siteTitle, link);
				return false;
			}
		}
		
		/**
		 * Deselect the current site for copying
		 *
		 * 
		 * 
		 * @param string slotName	The slot name to copy
		 * @param string siteId		The site id selected
		 * @param string siteTitle	The title of the site selected
		 * @param DOMElement link	The link clicked
		 * @access public
		 * @since 7/25/08
		 */
		Portal.deselectForCopy = function (slotName, siteId, siteTitle, link) {
			// Remove 'paste' links to all of the placeholders 
			var copyAreas = document.getElementsByClassName('portal_slot_copy_area');
			for (var i = 0; i < copyAreas.length; i++) {
				var area = copyAreas[i];
				area.style.display = 'none';
			}
			
			// Send off an asynchronous request to record the deselection of the slotname
			// for future page-loads
			if (Portal.selectedSlotname && Portal.selectedSlotname == slotName)
				Portal.unsetSelectedSlotname();
			
			// Change the link to a select select link
			link.innerHTML = '"._('select for copy')."';
			link.onclick = function () {
				Portal.selectForCopy(slotName, siteId, siteTitle, link);
				return false;
			}
		}
		
		/**
		 * Copy the selected site into a slot
		 * 
		 * @param string slotName	The slot name to copy
		 * @param string srcSiteId
		 * @param string srcTitle
		 * @param DOMElement link	The link clicked
		 * @return void
		 * @access public
		 * @since 7/28/08
		 */
		Portal.copyToSlot = function (slotName, link) {
			if (!Portal.selectedSiteId)
				throw 'Portal.selectedSiteId has no value';
			
			if (!Portal.selectedSiteTitle)
				throw 'Portal.selectedSiteTitle has no value';
				
			SiteCopyPanel.run(slotName, Portal.selectedSiteId, Portal.selectedSiteTitle, link);
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
			if (!isset($_SESSION['portal_slot_selection']) || !$_SESSION['portal_slot_selection'])
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
				$selectedSlot = $this->getSelectedSlot();
				$selectedTitle = str_replace('"', '&quot;', HtmlString::getSafeHtml($this->getSelectedSiteTitle()));
				$siteId = $selectedSlot->getSiteAsset()->getId()->getIdString();
				$display = 'inline';
			} catch (OperationFailedException $e) {
				$selectedTitle = '';
				$siteId = '';
				$display = 'none';
			}
			print "<span class='portal_slot_copy_area' style='display: ".$display."'> | <a href='#' class='portal_slot_copy_link' onclick=\"Portal.copyToSlot('".$slot->getShortname()."', this); return false;\">".str_replace('%1', $selectedTitle, _("copy '%1' here..."))."</a></span>";
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
			// Since view AZs cascade up, just check at the node.
			if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $slot->getSiteId()))
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
		$viewUrl = SiteDispatcher::getSitesUrlForSiteId($assetId->getIdString());
		
		$slotManager = SlotManager::instance();
		try {
			$slot = $slotManager->getSlotBySiteId($assetId);
		} catch (Exception $e) {
		}
		
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
		print "\n\t\t<br/>";
		print "\n\t\t<a href='".$viewUrl."' style='font-size: smaller;'>";
		print "\n\t\t\t".$viewUrl;
		print "\n\t\t</a>";
		print "\n\t</div>";
		
		print "\n\t<div class='portal_list_controls'>\n\t\t";
		$controls = array();
		
		$controls[] = "<a href='".$viewUrl."'>"._("view")."</a>";
		
		// While it is more correct to check modify permission permission, doing
		// so forces us to check AZs on the entire site until finding a node with
		// authorization or running out of nodes to check. Since edit-mode actions
		// devolve into view-mode if no authorization is had by the user, just
		// show the links all the time to cut page loads from 4-6 seconds to
		// less than 1 second.
		$controls[] = "<a href='".$harmoni->request->quickURL($action->getUiModule(), 'editview', array('node' => $assetId->getIdString()))."'>"._("edit")."</a>";
	
// 		if ($action->getUiModule() == 'ui2') {
// 			$controls[] = "<a href='".$harmoni->request->quickURL($action->getUiModule(), 'arrangeview', array('node' => $assetId->getIdString()))."'>"._("arrange")."</a>";
// 		}
		
		if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.delete'), $assetId))
			$controls[] = "<a href='".$harmoni->request->quickURL($action->getUiModule(), 'deleteComponent', array('node' => $assetId->getIdString()))."' onclick=\"if (!confirm('"._("Are you sure that you want to permenantly delete this site?")."')) { return false; }\">"._("delete")."</a>";
		
		
		// Add a control to select this site for copying. This should probably
		// have its own authorization, but we'll use add_children/modify for now.
		if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.modify'), $assetId)) 
		{
			if (isset($slot) && isset($_SESSION['portal_slot_selection']) && $_SESSION['portal_slot_selection'] == $slot->getShortname()) {
				$controls[] = "<a href='#' onclick=\"Portal.deselectForCopy('".$slot->getShortname()."', '".$assetId->getIdString()."', '".addslashes(str_replace('"', '&quot;', HtmlString::getSafeHtml($asset->getDisplayName())))."', this); return false;\" class='portal_slot_select_link'>"._("cancel copy")."</a>";
			} else if (isset($slot)) {
				$controls[] = "<a href='#' onclick=\"Portal.selectForCopy('".$slot->getShortname()."', '".$assetId->getIdString()."', '".addslashes(str_replace('"', '&quot;', HtmlString::getSafeHtml($asset->getDisplayName())))."', this); return false;\" class='portal_slot_select_link'>"._("select for copy")."</a>";
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