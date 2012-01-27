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
require_once(POLYPHONY.'/main/modules/user/UserDataHelper.class.php');

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
	
		print $this->getTitleText();
		print "\n\t<div style='clear: both; height: 0px;'>&nbsp;</div>";
		
		return ob_get_clean();
	}
	
	/**
	 * Return the title-text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 2/9/09
	 */
	function getTitleText () {
		ob_start();
		$authN = Services::getService("AuthN");
		
		// get current category/folder and put into header
		$portalMgr = PortalManager::instance();
		$currentFolder = $portalMgr->getFolder($this->getCurrentFolderId());
		
		if ($authN->isUserAuthenticatedWithAnyType()) {
			print _("Your Portal");
			print " &raquo; ".$currentFolder->getDisplayName();
		} else {
			print _("Portal (log in to see your own portal)");
		}
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
		
		// Store the login state for determining whether or not to show edit links.
		$authN = Services::getService("AuthN");
		$this->isAuthenticated = $authN->isUserAuthenticatedWithAnyType();
		
		$this->addHeadJs();
		
		// Add the selection Panel
		Segue_Selection::instance()->addHeadJavascript();
		
		$harmoni = Harmoni::instance();
		$harmoni->attachData('help_topic', 'Portal');
		
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
		$portalWrapper->add(new Block(ob_get_clean(), STANDARD_BLOCK), "175px", null, CENTER, TOP);
		
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
		UserDataHelper::writeHeadJs();
		
		$harmoni = Harmoni::instance();
		ob_start();
		
		print "\n
 		<script type='text/javascript' src='".MYPATH."/javascript/scriptaculous-js/lib/prototype.js'></script>
		<script type='text/javascript' src='".MYPATH."/javascript/scriptaculous-js/src/scriptaculous.js'></script>
		<script type='text/javascript' src='".POLYPHONY_PATH."javascript/CenteredPanel.js'></script>
		<script type='text/javascript' src='".MYPATH."/javascript/AliasPanel.js'></script>
		<script type='text/javascript' src='".MYPATH."/javascript/MigrationPanel.js'></script>
		<script type='text/javascript' src='".MYPATH."/javascript/ArchiveStatus.js'></script>
		
		<style type='text/css'>
			/* Other portal styles are in the static CSS file, images/SegueCommon.css */
			ul.portal_folders li {
				list-style-image:  url(".MYPATH."/images/icons/16x16/folder_open.png);
			}
		</style>
		";
		
		$handler = $harmoni->getOutputHandler();
		$handler->setHead($handler->getHead().ob_get_clean());
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
					$this->currentFolderId = 'recent_access';
				}
			} else if (UserData::instance()->getPreference('segue_portal_last_folder')) {
				$harmoni = Harmoni::instance();
				$this->currentFolderId = UserData::instance()->getPreference('segue_portal_last_folder');
				
				if (!strlen(RequestContext::value('starting_number')) && UserData::instance()->getPreference('segue_portal_starting_number')) {
					
					$harmoni->request->setRequestParam('starting_number', UserData::instance()->getPreference('segue_portal_starting_number'));
				}
				
				try {
					$portalMgr = PortalManager::instance();
					$folder = $portalMgr->getFolder($this->currentFolderId);
					$this->currentFolderId = $folder->getIdString();
				} catch (UnknownIdException $e) {
					$this->currentFolderId = 'personal';
					$harmoni->request->setRequestParam('starting_number', '0');
				}
			} else {
				$this->currentFolderId = 'recent_access';
			}
		}
		
		UserData::instance()->setPreference('segue_portal_last_folder', $this->currentFolderId);
		UserData::instance()->setPreference('segue_portal_starting_number', strval(intval(RequestContext::value('starting_number'))));
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
		// Print an existing site.
		if ($slot->getSiteId()) {
			try {
				return $this->printSiteShort($slot->getSiteAsset(), $this, 0, $slot);
			} 
			// Cached slot may not know that it's site was deleted.
			catch(UnknownIdException $e) {
			}
		} 
		
		// If no site is created
		ob_start();
		
		
		if ($slot->isAlias()) {
			// Print out the content
		
			print "\n\t<div class='portal_list_slotname'>";
			print $slot->getShortname();
					
			$targets = array();
			$target = $slot->getAliasTarget();
			while ($target) {
				$targets[] = $target->getShortname();
				if ($target->isAlias())
					$target = $target->getAliasTarget();
				else
					$target = null;
			}
			
			print "\n<br/>";
			print str_replace('%1', implode(' &raquo; ', $targets), _("(an alias of %1)"));
			print "\n\t</div>";
		}

		$harmoni = Harmoni::instance();		
		print $slot->getShortname();
		print " - ";
		if ($slot->isUserOwner() && !$slot->isAlias()) {

			print " <a href='".$harmoni->request->quickURL($this->getUiModule(), 'add', array('slot' => $slot->getShortname()))."' class='create_site_link'>"._("create site")."</a>";
			
			print " | <a href='#' onclick='AliasPanel.run(\"".$slot->getShortname()."\", this); return false;' class='create_site_link'>"._("make alias")."</a>";
			
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
			
			print " | ".Segue_Selection::instance()->getSiteMoveCopyLink($slot);
		} else {
			print " <span class='site_not_created_message'>"._("No Site Created")."</span>";
		}
		
		if ($slot->isUserOwner() && $slot->isAlias()) {
			print "\n\t<div class='portal_list_controls'>\n\t\t";
			
			print "<a href='".$harmoni->request->quickURL('slots', 'remove_alias', array('slot' => $slot->getShortname()))."' onclick=\"if (!confirm('".str_replace("%1", $slot->getShortname(), str_replace("%2", $slot->getAliasTarget()->getShortname(), _("Are you sure that you want \\'%1\\' to no longer be an alias of \\'%2\\'?")))."')) { return false; }\">"._("remove alias")."</a>";
			
			print "\n\t</div>";
		}
		
		ob_start();
		$this->printMigrationStatus($slot);
		$export = ob_get_clean();
		if ($export) {
			print "\n<div class='export_controls'>".$export."</div>";
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
			} 
			// allow owners of aliases to see the alias, even if they can't see anything else.
			else if ($slot->isAlias() && $slot->isUserOwner()) {
				return true;
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
	public function printSiteShort(Asset $asset, $action, $num, Slot $otherSlot = null) {
		$harmoni = Harmoni::instance();
		$assetId = $asset->getId();
		
		$authZ = Services::getService('AuthZ');
		$idMgr = Services::getService('Id');
		
		if (!$authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view'), $assetId)
				&& !$otherSlot->isUserOwner())
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
			$sitesTrueSlot = $slotManager->getSlotBySiteId($assetId);
		} catch (Exception $e) {
		}
		
		// Print out the content
		ob_start();
		print "\n\t<div class='portal_list_slotname'>";
		if (isset($sitesTrueSlot)) {
			if (is_null($otherSlot) || $sitesTrueSlot->getShortname() == $otherSlot->getShortname()) {
				print $sitesTrueSlot->getShortname();
			} else {
				print $otherSlot->getShortname();
				
				$targets = array();
				$target = $otherSlot->getAliasTarget();
				while ($target) {
					$targets[] = $target->getShortname();
					if ($target->isAlias())
						$target = $target->getAliasTarget();
					else
						$target = null;
				}
				
				print "\n<br/>";
				print str_replace('%1', implode(' &raquo; ', $targets), _("(an alias of %1)"));
				// Add Alias info.
// 				if ($otherSlot->isAlias()) {
// 					ob_start();
// 					
// 					print _("This slot is an alias of ").$slot->getAliasTarget()->getShortname();
// 					
// 					$container->add(new UnstyledBlock(ob_get_clean()), "100%", null, LEFT, TOP);
// 				}
			}
		} else {
			print _("ID#").": ".$assetId->getIdString();
		}
		print "\n\t</div>";
		print "\n\t<div class='portal_list_site_title'>";
		if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view'), $assetId)) {
			print "\n\t\t<a href='".$viewUrl."'>";
			print "\n\t\t\t<strong>".HtmlString::getSafeHtml($asset->getDisplayName())."</strong>";
			print "\n\t\t</a>";
			print "\n\t\t<br/>";
			print "\n\t\t<a href='".$viewUrl."' style='font-size: smaller;'>";
			print "\n\t\t\t".$viewUrl;
			print "\n\t\t</a>";
		}
		print "\n\t</div>";
		
		print "\n\t<div class='portal_list_controls'>\n\t\t";
		$controls = array();
		
		if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view'), $assetId)) {
			$controls[] = "<a href='".$viewUrl."'>"._("view")."</a>";
		}
		
		// Hide all edit links if not authenticated to prevent web spiders from traversing them
		if ($this->isAuthenticated) {
			// While it is more correct to check modify permission permission, doing
			// so forces us to check AZs on the entire site until finding a node with
			// authorization or running out of nodes to check. Since edit-mode actions
			// devolve into view-mode if no authorization is had by the user, just
			// show the links all the time to cut page loads from 4-6 seconds to
			// less than 1 second.
			if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view'), $assetId)) {
				$controls[] = "<a href='".SiteDispatcher::quickURL($action->getUiModule(), 'editview', array('node' => $assetId->getIdString()))."'>"._("edit")."</a>";
			}
		
	// 		if ($action->getUiModule() == 'ui2') {
	// 			$controls[] = "<a href='".SiteDispatcher::quickURL($action->getUiModule(), 'arrangeview', array('node' => $assetId->getIdString()))."'>"._("arrange")."</a>";
	// 		}
			
			// add link to tracking
			if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view'), $assetId)) {
				$trackUrl = $harmoni->request->quickURL("participation", "actions", array('node' => $assetId->getIdString()));
				ob_start();
				print " <a target='_blank' href='".$trackUrl."'";				
				print ' onclick="';
				print "var url = '".$trackUrl."'; ";
				print "window.open(url, 'site_map', 'width=600,height=600,resizable=yes,scrollbars=yes'); ";
				print "return false;";
				print '"';
				print ">"._("track")."</a>";
				$controls[] = ob_get_clean();
			}	
			
			if (!is_null($otherSlot) && $otherSlot->isAlias() && $otherSlot->isUserOwner()) {
				$controls[] = "<a href='".$harmoni->request->quickURL('slots', 'remove_alias', array('slot' => $otherSlot->getShortname()))."' onclick=\"if (!confirm('".str_replace("%1", $otherSlot->getShortname(), str_replace("%2", $otherSlot->getAliasTarget()->getShortname(), _("Are you sure that you want \\'%1\\' to no longer be an alias of \\'%2\\'?")))."')) { return false; }\">"._("remove alias")."</a>";
			} else if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.delete'), $assetId))
				$controls[] = "<a href='".$harmoni->request->quickURL($action->getUiModule(), 'deleteComponent', array('node' => $assetId->getIdString()))."' onclick=\"if (!confirm('"._("Are you sure that you want to permenantly delete this site?")."')) { return false; }\">"._("delete")."</a>";
			
			
			// Add a control to select this site for copying. This should probably
			// have its own authorization, but we'll use add_children/modify for now.
			if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.modify'), $assetId)) 
			{
				 if (isset($sitesTrueSlot) 
				 	&& (is_null($otherSlot) || $sitesTrueSlot->getShortname() == $otherSlot->getShortname())) 
				 {
					$controls[] = Segue_Selection::instance()->getAddLink(
					SiteDispatcher::getSiteDirector()->getSiteComponentFromAsset($asset));
				}
			}
		}
		
		print implode("\n\t\t | ", $controls);
		print "\n\t</div>";
		
		if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.view'), $assetId)) {
			$description = HtmlString::withValue($asset->getDescription());
			$description->trim(25);
			print  "\n\t<div class='portal_list_site_description'>".$description->asString()."</div>";	
		}
		print "\n\t<div style='clear: both;'></div>";
		
		print $this->getExportControls($assetId, $otherSlot, $sitesTrueSlot);
		
		$component = new UnstyledBlock(ob_get_clean());
		$container->add($component, "100%", null, LEFT, TOP);
		
		return $container;
	}
	
	/**
	 * Answer an HTML block of export controls.
	 * 
	 * @param Id $assetId
	 * @param optional Slot $slot
	 * @return string
	 */
	public function getExportControls (Id $assetId, Slot $slot = null, Slot $sitesTrueSlot = null) {
		if (!defined('DATAPORT_ENABLE_EXPORT_REDIRECT') || !DATAPORT_ENABLE_EXPORT_REDIRECT)
			return '';
		
		$authZ = Services::getService('AuthZ');
		$idMgr = Services::getService('Id');
		
		ob_start();
		if (!empty($slot))
			$this->printMigrationStatus($slot);
		
		// Export controls
		if ($authZ->isUserAuthorized($idMgr->getId('edu.middlebury.authorization.modify'), $assetId))  {
			print "\n\t<div class='portal_list_controls portal_list_export_controls'>\n\t\t";
			$controls = array();
			
			if (isset($sitesTrueSlot))
				$params = array('site' => $sitesTrueSlot->getShortname());
			else
				$params = array('node' => $assetId->getIdString());
			
			if ($this->isExportEnabled('html')) {
				$control = "<a href='".SiteDispatcher::quickURL('dataport', 'export_html', $params)."' onclick='new ArchiveStatus(this, \"".SiteDispatcher::quickURL('dataport', 'check_html_export', $params)."\");'>"._("export HTML archive")."</a>";
				
				if (!empty($GLOBALS['dataport_export_types']['html']['help'])) {
					$control .= " (<a href='".$GLOBALS['dataport_export_types']['html']['help']."' target='_blank'>help</a>)";
				}
				
				$controls[] = $control;
			}
			
			if ($this->isExportEnabled('wordpress')) {
				$control = "<a href='".SiteDispatcher::quickURL('dataport', 'wordpress', $params)."'>"._("export for wordpress")."</a>";
				
				if (!empty($GLOBALS['dataport_export_types']['wordpress']['help'])) {
					$control .= " (<a href='".$GLOBALS['dataport_export_types']['wordpress']['help']."' target='_blank'>help</a>)";
				}
				
				$controls[] = $control;
			}
			
			if ($this->isExportEnabled('files')) {
				$control = "<a href='".SiteDispatcher::quickURL('dataport', 'files', $params)."'>"._("export files")."</a>";
				
				if (!empty($GLOBALS['dataport_export_types']['files']['help'])) {
					$control .= " (<a href='".$GLOBALS['dataport_export_types']['files']['help']."' target='_blank'>help</a>)";
				}
				
				$controls[] = $control;
			}
			
			print implode("\n\t\t | ", $controls);
			print "\n\t</div>";
		}
		$export = ob_get_clean();
		if ($export) {
			return "\n<div class='export_controls'>".$export."</div>";
		} else {
			return '';
		}
	}
	
	/**
	 * Answer true if export is enabled for the export type and current user.
	 * 
	 * @param string $exportType
	 * @return boolean
	 */
	public function isExportEnabled ($exportType) {
		static $exportEnabled = array();
		if (isset($exportEnabled[$exportType]))
			return $exportEnabled[$exportType];
		
		if (!isset($GLOBALS['dataport_export_types'][$exportType])) {
			$exportEnabled[$exportType] = false;
			return false;
		}
		
		$settings = $GLOBALS['dataport_export_types'][$exportType];
		if (!is_array($settings)) {
			$exportEnabled[$exportType] = true;
			return true;
		}
		
		if (isset($settings['groups'])) {
			$authN = Services::getService('AuthN');
			$userId = $authN->getFirstUserId();
			$agentMgr = Services::getService('Agent');
			$agent = $agentMgr->getAgent($userId);
			$idMgr = Services::getService('Id');
			foreach ($settings['groups'] as $groupIdString) {
				$groupId = $idMgr->getId($groupIdString);
				$group = $agentMgr->getGroup($groupId);
				if ($group->contains($agent, true)) {
					$exportEnabled[$exportType] = true;
					return true;
				}
			}
			$exportEnabled[$exportType] = false;
			return false;
		}
		
		$exportEnabled[$exportType] = true;
		return true;
	}
	
	/**
	 * Answer an HTML block that describes the migration status of the slot
	 * 
	 * @param Slot $slot
	 * @return null
	 */
	public function printMigrationStatus (Slot $slot) {
		if (!defined('DATAPORT_ENABLE_EXPORT_REDIRECT') || !DATAPORT_ENABLE_EXPORT_REDIRECT)
			return;
		
		// Just work with the primary slot if aliases are involved.
		if ($slot->isAlias())
			$slot = $slot->getAliasTarget();
		
		print "\n\t<div class='portal_list_migration_status'>\n\t\t";
		print "Migration Status: ";
		$status = $slot->getMigrationStatus();
		print "<span class='status_line'>";
		switch ($status['type']) {
			case 'archived':
				print '<span class="status status_archived">Archived</span>';
				break;
			case 'migrated':
				print '<span class="status status_migrated">Migrated</span>';
				if (!empty($status['url'])) {
					print ' to <a href="'.htmlentities($status['url']).'">'.htmlentities($status['url']).'</a>';
				}
				break;
			case 'unneeded':
				if ($slot->siteExists())
					print '<span class="status status_unneeded">No Longer Needed</span>';
				else
					print '<span class="status status_unneeded_empty">No Longer Needed</span>';
				break;
			default:
				print '<span class="status status_incomplete">Incomplete</span>';
		}
		print " </span>";
		if ($this->canChangeSlotStatus($slot)) {
			print "<button onclick='MigrationPanel.run(\"".$slot->getShortname()."\", \"".$status['type']."\", \"".$status['url']."\", this); return false;' class='create_site_link'>"._("change")."</button>";
		}
		print "\n\t</div>";
	}
	
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/14/08
	 */
	protected function canChangeSlotStatus (Slot $slot) {
		if (!$slot->siteExists()) {
			return $slot->isUserOwner();
		}
		
		// get siteRoot node and check that
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		// Since view AZs cascade up, just check at the node.
		return $azMgr->isUserAuthorized(
			$idMgr->getId('edu.middlebury.authorization.modify'),
			$slot->getSiteId());
	}
}

?>