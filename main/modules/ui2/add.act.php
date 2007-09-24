<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add.act.php,v 1.8 2007/09/24 20:56:10 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add.act.php,v 1.8 2007/09/24 20:56:10 adamfranco Exp $
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
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		// :: Name and Description ::
		$step = $wizard->addStep("namedescstep", new WizardStep());
		$step->setDisplayName(_("Name &amp; Description"));
		
		// Create the properties.
		$displayNameProp = $step->addComponent("display_name", new WTextField());
		$displayNameProp->setErrorText("<nobr>"._("A value for this field is required.")."</nobr>");
		$displayNameProp->setErrorRule(new WECNonZeroRegex("[\\w]+"));
	// 	$displayNameProp->setDefaultValue(_("Default Asset Name"));
//		$displayNameProp->setErrorString(" <span style='color: #f00'>* "._("The name must not start with a space.")."</span>");
		
		$descriptionProp = $step->addComponent("description", WTextArea::withRowsAndColumns(5,30));
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
		
		return $wizard;
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
		$director = $this->getSiteDirector();
		$site = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'SiteNavBlock'), $null = null);
		
		$site->updateDisplayName($properties['namedescstep']['display_name']);
		$site->updateDescription($properties['namedescstep']['description']);
		
		$this->_siteId = $site->getId();
		$siteId = $idManager->getId($site->getId());
		
		/*********************************************************
		 * Save the siteId into the slot
		 *********************************************************/
		$slotMgr = SlotManager::instance();
		
		if (RequestContext::value('slot')) {
			$slot = $slotMgr->getSlotByShortname(RequestContext::value('slot'));
		} else {
			$authN = Services::getService("AuthN");
			$shortname = PersonalSlot::getPersonalShortname($authN->getFirstUserId());
			$slot = new PersonalSlot($shortname."_".$siteId);
			$slot->addOwner($authN->getFirstUserId());
		}
		
		$slot->setSiteId($siteId);
		
		
		/*********************************************************
		 * Set Default "All-Access" permissions for slot owners
		 *********************************************************/
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		$functions = array();
		$functions[] = $idMgr->getId("edu.middlebury.authorization.view_authorizations");
		$functions[] = $idMgr->getId("edu.middlebury.authorization.modify_authorizations");
		
		$functions[] = $idMgr->getId("edu.middlebury.authorization.add_children");
		$functions[] = $idMgr->getId("edu.middlebury.authorization.modify");
		$functions[] = $idMgr->getId("edu.middlebury.authorization.delete");
		$functions[] = $idMgr->getId("edu.middlebury.authorization.remove_children");
		
		$functions[] = $idMgr->getId("edu.middlebury.authorization.view");
		$functions[] = $idMgr->getId("edu.middlebury.authorization.comment");
		
		foreach ($slot->getOwners() as $ownerId) {
			foreach($functions as $functionId) {
				$authZ->createAuthorization($ownerId, $functionId, $siteId);
			}
		}
		
		
		/*********************************************************
		 * Create our default child assets
		 *********************************************************/
		$siteOrganizer = $site->getOrganizer();
		//$siteOrganizer->updateNumColumns('2');
		
		$siteOrganizer->updateNumRows('3');
		$siteOrganizer->updateNumColumns('1');

		$headerRowOrganizer = $director->createSiteComponent(new Type('segue', 'edu.middlebury', 'FlowOrganizer'), $siteOrganizer);
		$siteOrganizer->putSubcomponentInCell($headerRowOrganizer, 0);
		$headerRowOrganizer->updateShowDisplayNames ('false');
		$headerRowContent = $director->createSiteComponent(new Type('SeguePlugins', 'edu.middlebury', 'TextBlock'), $headerRowOrganizer);
		$headerRowContent->updateDisplayName(_('Site Header'));
		$headerRowContent->updateDescription(_('This is the header of this site, added by default.'));
		$headerRowContent->updateContentMarkup("<span style='font-size: large;'>".$properties['namedescstep']['display_name']."</span>");
		
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
			
			$item = new AgentNodeEntryItem("Create Site", "Site added");
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
	 * Set up our SiteDirector and make any needed data available
	 * 
	 * @return object SiteDirector
	 * @access public
	 * @since 4/14/06
	 */
	function getSiteDirector () {
		/*********************************************************
		 * XML Version
		 *********************************************************/
// 		$this->filename = MYDIR."/main/library/SiteDisplay/test/testSite.xml";
// 		
// 		$this->document = new DOMIT_Document();
// 		$this->document->setNamespaceAwareness(true);
// 		$success = $this->document->loadXML($this->filename);
// 
// 		if ($success !== true) {
// 			throwError(new Error("DOMIT error: ".$this->document->getErrorCode().
// 				"<br/>\t meaning: ".$this->document->getErrorString()."<br/>", "SiteDisplay"));
// 		}
// 
// 		$director = new XmlSiteDirector($this->document);
		
		
		/*********************************************************
		 * Asset version
		 *********************************************************/
		$repositoryManager = Services::getService('Repository');
		$idManager = Services::getService('Id');
		
		$director = new AssetSiteDirector(
			$repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository')));
		
		
		return $director;
	}
	
}

?>