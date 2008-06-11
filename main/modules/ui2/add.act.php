<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add.act.php,v 1.15 2008/03/31 20:07:47 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");
require_once(MYDIR."/main/library/Templates/TemplateManager.class.php");


/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add.act.php,v 1.15 2008/03/31 20:07:47 adamfranco Exp $
 */
class addAction 
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
		// Check that the user can create an asset here.
		if (RequestContext::value("slot")) {
			$slotMgr = SlotManager::instance();
			$slot = $slotMgr->getSlotByShortname(RequestContext::value("slot"));
			if ($slot->isUserOwner())
				return true;
			else
				return false;
		} else {
			$authZ = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			 
			return $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$idManager->getId("edu.middlebury.segue.sites_repository"));
		}
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to create a <em>Site</em> here.");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("parent_id");
		$harmoni->request->passthrough("slot");
		
		$centerPane = $this->getActionRows();
		$cacheName = 'add_site_wizard_'.RequestContext::value('parent_id');
		
		$this->runWizard ( $cacheName, $centerPane );
	}
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {	
		return _("Add a Site");
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 4/28/05
	 */
	function createWizard () {
		$idManager = Services::getService("Id");
		$repositoryManager = Services::getService("Repository");
		$repository = $repositoryManager->getRepository(
			$idManager->getId("edu.middlebury.segue.sites_repository"));
		
		// Instantiate the wizard, then add our steps.
		$wizard = RequiredStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step = $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Name &amp; Description"));
		
		// Create the properties.
		$displayNameProp = $step->addComponent("display_name", new WTextField());
		$displayNameProp->setErrorText("<span style='white-space: nowrap'>"._("A value for this field is required.")."</span>");
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
		$displayNameProp->setSize(80);
		
		$descriptionProp = $step->addComponent("description", WTextArea::withRowsAndColumns(5,80));
	// 	$descriptionProp->setDefaultValue(_("Default Asset description."));
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Name")."</h2>";
		print "\n"._("The Name for this <em>Site</em>: ");
		print "\n<br />[[display_name]]";
		print "\n<h2>"._("Description")."</h2>";
		print "\n"._("The Description for this <em>Site</em>: ");
		print "\n<br />[[description]]";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		// Site Admins.
		$this->addSiteAdminStep($wizard);
		
		// Template
		$this->addTemplateStep($wizard);

		
		return $wizard;
	}
	
	/**
	 * Add any additional site admins to a multi-select.
	 * 
	 * @param object Wizard $wizard
	 * @return void
	 * @access protected
	 * @since 1/28/08
	 */
	protected function addSiteAdminStep (Wizard $wizard) {
		/*********************************************************
		 * Owner step if multiple owners
		 *********************************************************/
		$step = new WizardStep();
		$step->setDisplayName(_("Choose Admins"));	
		
		$property = $step->addComponent("admins", new WMultiSelectList);
		
		$agentMgr = Services::getService("Agent");
		$i = 0;
		$owners = $this->getOwners();
		foreach ($owners as $ownerId) {
			$i++;
			$owner = $agentMgr->getAgent($ownerId);
			$property->addOption($ownerId->getIdString(), htmlspecialchars($owner->getDisplayName()));
			$property->setValue($ownerId->getIdString());
		}
		$property->setSize($i);
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Choose Site Admins")."</h2>";
		print "\n<p>"._("The following users are listed as owners of this placeholder. Keep them selected if you would like them be administrators of this site or de-select them if they should not be administrators of this site. Any choice made now can be changed later through the 'Permissions' screen for the site.<br/><br/>Hold down the CTRL key (Windows) or the COMMAND key (Mac) to select multiple users.");
		print "\n<br />[[admins]]</p>";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		if ($i) {
			$step = $wizard->addStep("owners", $step);
			$wizard->makeStepRequired('owners');
		}
	}
	
	/**
	 * Answer a list of owners to add to the Site Admins step.
	 *
	 * @return array
	 * @access protected
	 * @since 1/28/08
	 */
	protected function getOwners () {
		// Filter out the current user Id
		$slot = $this->getSlot();
		$authN = Services::getService("AuthN");
		$userId = $authN->getFirstUserId();
		$allOwners = $slot->getOwners();
		$owners = array();
		foreach ($allOwners as $ownerId) {
			if (!$userId->isEqual($ownerId))
				$owners[] = $ownerId;
		}
		return $owners;
	}
	
	/**
	 * Add the Template step to the wizard
	 * 
	 * @param object Wizard $wizard
	 * @return void
	 * @access protected
	 * @since 6/10/08
	 */
	protected function addTemplateStep (Wizard $wizard) {
		ob_start();
		
		$step = new WizardStep();
		$step->setDisplayName(_("Choose Template"));	
		
		print "\n<h2>"._("Choose Template")."</h2>";
		
		$property = $step->addComponent('template', new WRadioList);
		$templateMgr = Segue_Templates_TemplateManager::instance();
		$templates = $templateMgr->getTemplates();
		$property->setValue($templates[0]->getIdString());
		foreach ($templates as $template) {
			$property->addOption($template->getIdString(), "<strong>".$template->getDisplayName()."</strong>", "<div>".$template->getDescription()."</div>");
		}
		
		print '[[template]]';
		
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		$step = $wizard->addStep("template", $step);
		$wizard->makeStepRequired('template');
	}
		
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		$wizard = $this->getWizard($cacheName);
		
		if (!$wizard->validate()) return false;
		
		$idManager = Services::getService("Id");
		$properties = $wizard->getAllValues();
		
		/*********************************************************
		 * Create the site Asset
		 *********************************************************/			
		$director = SiteDispatcher::getSiteDirector();
		$site = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'SiteNavBlock'), $null = null);
		
		$site->updateDisplayName($properties['namedescstep']['display_name']);
		$site->updateDescription($properties['namedescstep']['description']);
		
		$this->_siteId = $site->getId();
		$siteId = $idManager->getId($site->getId());
		
		/*********************************************************
		 * Save the siteId into the slot
		 *********************************************************/
		$slot = $this->getSlot();
		
		$slot->setSiteId($siteId);
		
		
		/*********************************************************
		 * // Check the Role of the creator and make sure it is 'admin'
		 *********************************************************/
		$roleMgr = SegueRoleManager::instance();
		$role = $roleMgr->getUsersRole($site->getQualifierId(), true);
		$admin = $roleMgr->getRole('admin');
		if ($role->isLessThan($admin))
			$admin->applyToUser($site->getQualifierId(), true);
			
		/*********************************************************
		 * Set Default "All-Access" permissions for slot owners
		 *********************************************************/
		foreach ($slot->getOwners() as $ownerId) {
			// If we have an 'owners' step, only make the owners chosen admins.
			if (isset($properties['owners']) 
				&& in_array($ownerId->getIdString(), $properties['owners']['admins'])) 
			{
				$role = $roleMgr->getAgentsRole($ownerId, $site->getQualifierId(), true);
				if ($role->isLessThan($admin))
					$admin->apply($ownerId, $site->getQualifierId(), true);
			}
		}
		
		
		
		
		/*********************************************************
		 * Create our default child assets
		 *********************************************************/
		$siteOrganizer = $site->getOrganizer();
		//$siteOrganizer->updateNumColumns('2');
		
		$siteOrganizer->updateNumRows('3');
		$siteOrganizer->updateNumColumns('1');
		
		//--------------------------------------------------------
		// Header
		//--------------------------------------------------------
		
		// Outer layout organizer
		$outerHeaderLayout = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'FixedOrganizer'), $siteOrganizer);
		$siteOrganizer->putSubcomponentInCell($outerHeaderLayout, 0);
		$outerHeaderLayout->updateNumRows('2');
		$outerHeaderLayout->updateNumColumns('1');
		
		// Header Content Organizer
		$headerRowOrganizer = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'FlowOrganizer'), $outerHeaderLayout);
		$outerHeaderLayout->putSubcomponentInCell($headerRowOrganizer, 0);
		$headerRowOrganizer->updateShowDisplayNames ('false');
		$headerRowOrganizer->updateShowHistorySetting ('false');
		$headerRowOrganizer->updateCommentsEnabled ('false');
		$headerRowOrganizer->updateShowDatesSetting ('none');
		$headerRowOrganizer->updateShowAttributionSetting ('none');
		
		// Header Content
		$headerRowContent = $director->createSiteComponent(new Type('SeguePlugins', 'edu.middlebury', 'TextBlock'), $headerRowOrganizer);
		$headerRowContent->updateDisplayName(_('Site Header'));
		$headerRowContent->updateDescription(_('This is the header of this site, added by default.'));
		$headerRowContent->updateContentMarkup("<span style='font-size: large;'>".$properties['namedescstep']['display_name']."</span>");
		
		// Layout Organizer for breadcrumbs and RSS
		$statusLayout = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'FixedOrganizer'), $outerHeaderLayout);
		$outerHeaderLayout->putSubcomponentInCell($statusLayout, 1);
		$statusLayout->updateNumRows('1');
		$statusLayout->updateNumColumns('2');
		
		// Breadcrumbs Content Organizer
		$breadcrumbsOrganizer = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'FlowOrganizer'), $statusLayout);
		$statusLayout->putSubcomponentInCell($breadcrumbsOrganizer, 0);
		$breadcrumbsOrganizer->updateShowDisplayNames ('false');
		$breadcrumbsOrganizer->updateShowHistorySetting ('false');
		$breadcrumbsOrganizer->updateCommentsEnabled ('false');
		$breadcrumbsOrganizer->updateShowDatesSetting ('none');
		$breadcrumbsOrganizer->updateShowAttributionSetting ('none');
		
		// Breadcrumbs
		$breadcrumbs = $director->createSiteComponent(new Type('SeguePlugins', 'edu.middlebury', 'Breadcrumbs'), $breadcrumbsOrganizer);
		$breadcrumbs->updateDisplayName(_("Breadcrumbs"));
		
		// RSS Content Organizer
		$rssOrganizer = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'FlowOrganizer'), $statusLayout);
		$statusLayout->putSubcomponentInCell($rssOrganizer, 1);
		$rssOrganizer->updateShowDisplayNames ('false');
		$rssOrganizer->updateShowHistorySetting ('false');
		$rssOrganizer->updateCommentsEnabled ('false');
		$rssOrganizer->updateShowDatesSetting ('none');
		$rssOrganizer->updateShowAttributionSetting ('none');
		$rssOrganizer->updateWidth ('250px');
		
		// Breadcrumbs
		$rss = $director->createSiteComponent(new Type('SeguePlugins', 'edu.middlebury', 'Rsslinks'), $rssOrganizer);
		$rss->updateDisplayName(_("RSS Links"));
		
		
		//--------------------------------------------------------
		// Main Menu
		//--------------------------------------------------------
		$MainOrganizer = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'FixedOrganizer'), $siteOrganizer);
		$siteOrganizer->putSubcomponentInCell($MainOrganizer, 1);
		$MainOrganizer->updateNumColumns('2');
		
		$mainMenu = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'MenuOrganizer'), $MainOrganizer);
		$mainMenu->updateWidth("200px");
		$MainOrganizer->putSubcomponentInCell($mainMenu, 0);
		$menuTarget = $MainOrganizer->getId()."_cell:1";
		$mainMenu->updateTargetId($menuTarget);
		$mainMenu->updateDirection('Top-Bottom/Left-Right');
		
		
		
// 		$mainMenu = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'MenuOrganizer'), $siteOrganizer);
// 		$siteOrganizer->putSubcomponentInCell($mainMenu, 0);
// 		$menuTarget = $siteOrganizer->getId()."_cell:1";
// 		$mainMenu->updateTargetId($menuTarget);
// 		$mainMenu->updateDirection('Top-Bottom/Left-Right');
		
		//--------------------------------------------------------
		// Page 1
		//--------------------------------------------------------
		$page1 = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'NavBlock'), $mainMenu);
		$page1->updateDisplayName(_('My First Page'));
		$page1->updateDescription(_('This is the first page in the site, added by default.'));
		
		$page1Org = $page1->getOrganizer();
		$page1ContentOrg = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'FlowOrganizer'), $page1Org);
		$page1Org->putSubcomponentInCell($page1ContentOrg, 0);
		
		$page1Content = $director->createSiteComponent(new Type('SeguePlugins', 'edu.middlebury', 'TextBlock'), $page1ContentOrg);
		$page1Content->updateDisplayName(_('My First Content'));
		$page1Content->updateDescription(_('This is the first content in this page, added by default.'));
		
		$page1Content = $director->createSiteComponent(new Type('SeguePlugins', 'edu.middlebury', 'TextBlock'), $page1ContentOrg);
		$page1Content->updateDisplayName(_('My Second Content'));
		$page1Content->updateDescription(_('This is the second content in this page, added by default.'));
		
		
		//--------------------------------------------------------
		// Page 2
		//--------------------------------------------------------
		$page2 = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'NavBlock'), $mainMenu);
		$page2->updateDisplayName(_('My Second Page'));
		$page2->updateDescription(_('This is the second page in the site, added by default.'));
		
		$page2Org = $page2->getOrganizer();
		$page2ContentOrg = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'FlowOrganizer'), $page2Org);
		$page2Org->putSubcomponentInCell($page2ContentOrg, 0);
		
		$page2Content = $director->createSiteComponent(new Type('SeguePlugins', 'edu.middlebury', 'TextBlock'), $page2ContentOrg);
		$page2Content->updateDisplayName(_('My Third Content'));
		$page2Content->updateDescription(_('This is the first content in this page, added by default.'));
		
		$page2Content = $director->createSiteComponent(new Type('SeguePlugins', 'edu.middlebury', 'TextBlock'), $page2ContentOrg);
		$page2Content->updateDisplayName(_('My Fourth Content'));
		$page2Content->updateDescription(_('This is the second content in this page, added by default.'));
		
		//--------------------------------------------------------
		// Footer
		//--------------------------------------------------------
		$footerRowOrganizer = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'FlowOrganizer'), $siteOrganizer);
		$siteOrganizer->putSubcomponentInCell($footerRowOrganizer, 2);
		$footerRowOrganizer->updateShowDisplayNames ('false');
// 		$footerRowContent = $director->createSiteComponent(new Type('SeguePlugins', 'edu.middlebury', 'TextBlock'), $footerRowOrganizer);
// 		$footerRowContent->updateDisplayName(_('My Footer'));
// 		$footerRowContent->updateDescription(_('This is the footer of this site, added by default.'));
// 		$footerRowContent->updateContentMarkup(_('My Footer'));
		
		
		/*********************************************************
		 * Log the success or failure
		 *********************************************************/
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item = new AgentNodeEntryItem("Create Site", "Site added for placeholder, '".$slot->getShortname()."'.");
			$item->addNodeId($siteId);
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		return TRUE;
	
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
		if ($this->_siteId) 
			return $harmoni->request->quickURL($harmoni->request->getRequestedModule(), "editview", array(
				"node" => $this->_siteId));
		else
			return $harmoni->request->quickURL('portal', "list");
	}
	
	/**
	 * Answer the slot object
	 *
	 * @return object Slot
	 * @access protected
	 * @since 1/14/08
	 */
	protected function getSlot () {
		$slotMgr = SlotManager::instance();
		
		if (RequestContext::value('slot')) {
			$slot = $slotMgr->getSlotByShortname(RequestContext::value('slot'));
		} else {
			$authN = Services::getService("AuthN");
			$shortname = PersonalSlot::getPersonalShortname($authN->getFirstUserId());
			$slot = new PersonalSlot($shortname."-".$siteId);
			$slot->addOwner($authN->getFirstUserId());
		}
		return $slot;
	}
}

?>