<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueClassicWizard.abstract.php,v 1.18 2008/01/10 21:03:29 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/library/SiteDisplay/SiteComponents/AssetSiteComponents/AssetSiteDirector.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/IsAuthorizableVisitor.class.php");

/**
 * This is an abstract action class with common functionality for all Segue
 * classic-mode wizards
 * 
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueClassicWizard.abstract.php,v 1.18 2008/01/10 21:03:29 adamfranco Exp $
 */
class SegueClassicWizard
	extends MainWindowAction
{
		
	/**
	 * Answer true if the user is authorized to run this wizard.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/8/07
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		$authZ = Services::getService("AuthZ");
		return $authZ->isUserAuthorized(
					$this->getAuthFunctionId(), 
					$this->getQualifierId());
	}
	
	/**
	 * Answer the authorization function Id to use when checking authorizations
	 * 
	 * @return object Id
	 * @access public
	 * @since 5/8/07
	 */
	function getAuthFunctionId () {
		$idManager = Services::getService("Id");
		return $idManager->getId("edu.middlebury.authorization.modify");
	}
	
	/**
	 * Answer the qualifier Id to use when checking authorizations
	 * 
	 * @return object Id
	 * @access public
	 * @since 5/8/07
	 */
	function getQualifierId () {
		$component = $this->getSiteComponent();
		return $component->getQualifierId();
	}
	
	/**
	 * Answer the site component that we are editing. If this is a creation wizard
	 * then null will be returned.
	 * 
	 * @return mixed object SiteComponent or null
	 * @access public
	 * @since 5/8/07
	 */
	function getSiteComponent () {
		$idManager = Services::getService("Id");
		return $this->getSiteComponentForId(
			$idManager->getId(RequestContext::value("node")));
	}
	
	/**
	 * Answer the site component for a given Id
	 * 
	 * @param object Id $id
	 * @return object SiteComponent
	 * @access public
	 * @since 5/8/07
	 */
	function getSiteComponentForId ( $id ) {
		$director = $this->getSiteDirector();
		return $director->getSiteComponentById($id->getIdString());
	}
	
	/**
	 * Answer the site component for a given Id string
	 * 
	 * @param string $id
	 * @return object SiteComponent
	 * @access public
	 * @since 6/4/07
	 */
	function getSiteComponentForIdString ( $id ) {
		$director = $this->getSiteDirector();
		return $director->getSiteComponentById($id);
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 5/8/07
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("node");
		$harmoni->request->passthrough("returnNode");
		$harmoni->request->passthrough("returnAction");
		
		$centerPane = $this->getActionRows();
		$qualifierId = $this->getQualifierId();
		$cacheName = get_class($this).'_'.$qualifierId->getIdString();
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("node");
		$harmoni->request->passthrough("returnNode");
		$harmoni->request->passthrough("returnAction");
		
		$this->runWizard ( $cacheName, $centerPane );
	}
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 5/8/07
	 */
	function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		$wizard->addStep("namedesc", $this->getTitleStep());
// 		try {
// 			$wizard->addStep("permissions", $this->getPermissionsStep());
// 		} catch (PermissionDeniedException $e) {
// 		
// 		}
		$wizard->addStep("display", $this->getDisplayOptionsStep());
// 		$wizard->addStep("status", $this->getStatusStep());
		
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
	 * @since 5/9/07
	 */
	function saveWizard ( $cacheName ) {
		$wizard = $this->getWizard($cacheName);
		
		// If all properties validate then go through the steps nessisary to
		// save the data.
		if ($wizard->validate()) {
			$properties = $wizard->getAllValues();
			
			if (!$this->saveTitleStep($properties['namedesc']))
				return FALSE;
			
			if (isset($properties['permissions']))
				if (!$this->savePermissionsStep($properties['permissions']))
					return FALSE;
			
			if (!$this->saveDisplayOptionsStep($properties['display']))
				return FALSE;
			
			
			if (!$this->saveStatusStep($properties['status']))
				return FALSE;
			
			/*********************************************************
			 * Log the event
			 *********************************************************/
			if (Services::serviceRunning("Logging")) {
				$loggingManager = Services::getService("Logging");
				$log = $loggingManager->getLogForWriting("Segue");
				$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
								"A format in which the acting Agent[s] and the target nodes affected are specified.");
				$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
								"Normal events.");
				
				$siteComponent = $this->getSiteComponent();
				
				$item = new AgentNodeEntryItem("Component Modified", $siteComponent->getComponentClass()." modified.");
				
				$item->addNodeId($siteComponent->getQualifierId());
				$site = $siteComponent->getDirector()->getRootSiteComponent($siteComponent->getId());
				if (!$siteComponent->getQualifierId()->isEqual($site->getQualifierId()))
					$item->addNodeId($site->getQualifierId());
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Answer the url to return to
	 * 
	 * @return string
	 * @access public
	 * @since 5/8/07
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
		$harmoni->request->forget("returnNode");
		$harmoni->request->forget("returnAction");
		return $harmoni->request->quickURL(
			'ui1', $harmoni->request->get("returnAction"),
			array('node' => $harmoni->request->get("returnNode")));
	}
	
	/**
	 * Set up our SiteDirector and make any needed data available
	 * 
	 * @return object SiteDirector
	 * @access public
	 * @since 4/14/06
	 */
	function getSiteDirector () {
			if (!isset($this->_director)) {
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
			
			$this->_director = new AssetSiteDirector(
				$repositoryManager->getRepository(
					$idManager->getId('edu.middlebury.segue.sites_repository')));
		}
		
		return $this->_director;
	}
	
/*********************************************************
 * Steps
 *********************************************************/

	
	/**
	 * Create the step for adding the title and description
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/8/07
	 */
	function getTitleStep () {
		$component = $this->getSiteComponent();
		
		$step = new WizardStep();
		$step->setDisplayName(_("Title &amp; Description"));
		
		// Create the step text
		ob_start();
		
		$property = $step->addComponent("display_name", new WTextField());
		$property->setSize(80);
		if ($component)
			$property->setValue($component->getDisplayName());
		$property->setErrorText(_("A value for this field is required."));
		$property->setErrorRule(new WECNonZeroRegex("[\\w]+"));

		print "\n<p><strong>"._("Title:")."</strong>";
// 		print "\n"._("The title of content: ");
		print "\n<br />[[display_name]]</p>";
		
		
		$property = $step->addComponent("description", WTextArea::withRowsAndColumns(4,80));
		if ($component)
			$property->setValue($component->getDescription());
		print "\n<p><strong>"._("Description:")."</strong>";
// 		print "\n"._("The Description for this content: ");
		print "\n<br/>[[description]]";
		print "</p>";
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		return $step;
	}
	
	/**
	 * save the name and description step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/9/07
	 */
	function saveTitleStep ($values) {
		$component = $this->getSiteComponent();
		
		$value = trim($values['display_name']);
		if (!$value)
			return false;
		$component->updateDisplayName($value);
		
		$value = trim($values['description']);
		$component->updateDescription($value);
		
		return true;
	}
	
	/**
	 * Create the step for adding the display options.
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/8/07
	 */
	function getDisplayOptionsStep () {
		$component = $this->getSiteComponent();
		
		$step = new WizardStep();
		$step->setDisplayName(_("Display Options"));
		
		// Create the step text
		ob_start();
		
		$property = $step->addComponent("show_titles", new WSelectList());
		$this->addTitlesOptions($property);
		
		if ($component) {
			$val = $component->showDisplayNames();
			if ($val === true)
				$property->setValue('true');
			else if ($val === false)
				$property->setValue('false');
			
			$parent = $component->getParentComponent();
		} else {
			$parent = null;
		}
		

		print "\n<p><strong>"._("Display Content Titles:")."</strong> ";
		print "\n[[show_titles]]";
		
		if ($parent) {
			print "\n<br/>".str_replace('%1', 
				(($parent->showDisplayNames())?_('show'):_('hide')),
				_("Current default setting: %1"));
		}
		print "\n</p>";
		
		// Show history
		$property = $step->addComponent("show_history", new WSelectList());
		$this->addTitlesOptions($property);
		
		if ($component) {
			$val = $component->showHistorySetting();
			if ($val === true)
				$property->setValue('true');
			else if ($val === false)
				$property->setValue('false');
			
			$parent = $component->getParentComponent();
		} else {
			$parent = null;
		}
		

		print "\n<p><strong>"._("Display 'History' link:")."</strong> ";
		print "\n[[show_history]]";
		
		if ($parent) {
			print "\n<br/>".str_replace('%1', 
				(($parent->showHistory())?_('show'):_('hide')),
				_("Current default setting: %1"));
		}
		print "\n<br/><span style='font-size: smaller;'>";
		print _("This setting will cause the 'history' link to be shown or hidden when any user views this part of the site. The 'history' link will always be shown when users edit this part of the site.");
		print "</span>";
		print "\n</p>";
		
		
		// Enable Comments
		$property = $step->addComponent("enable_comments", new WSelectList());
		$this->addCommentsOptions($property);
		
		if ($component) {
			$val = $component->commentsEnabled();
			if ($val === true)
				$property->setValue('true');
			else if ($val === false)
				$property->setValue('false');
		}
		
		print "\n<p><strong>"._("Enable Comments:")."</strong> ";
		print "\n[[enable_comments]]";
		if ($parent) {
			print "\n<br/>".str_replace('%1', 
				(($parent->showComments())?_('enabled'):_('disabled')),
				_("Current default setting: %1"));
		}
		print "\n</p>";
		
// 		$this->printWidth($component, $step);
		
		$step->setContent(ob_get_clean());
		
		return $step;
	}
	
	/**
	 * Add the display titles options to the property
	 * 
	 * @param object WSelectList $property
	 * @return void
	 * @access public
	 * @since 11/30/07
	 */
	public function addTitlesOptions (WSelectList $property) {
		$property->addOption('default', _("use default"));
		$property->addOption('true', _("override-show"));
		$property->addOption('false', _("override-hide"));
		$property->setValue('default');
	}
	
	/**
	 * Add the comments options to the property
	 * 
	 * @param object WSelectList $property
	 * @return void
	 * @access public
	 * @since 11/30/07
	 */
	public function addCommentsOptions (WSelectList $property) {
		$property->addOption('default', _("use default"));
		$property->addOption('true', _("override-enable"));
		$property->addOption('false', _("override-disable"));
		$property->setValue('default');
	}
	
	
	/**
	 * save the display options step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/9/07
	 */
	function saveDisplayOptionsStep ($values) {
		$component = $this->getSiteComponent();
		$component->updateShowDisplayNames($values['show_titles']);
		$component->updateShowHistorySetting($values['show_history']);
		$component->updateCommentsEnabled($values['enable_comments']);
		$this->saveWidth($component, $values);
		return true;
	}
	
	/**
	 * Create the step for status options.
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/8/07
	 */
	function getStatusStep () {
		$component = $this->getSiteComponent();
		
		$step = new WizardStep();
		$step->setDisplayName(_("Status"));
		
		// Create the step text
		ob_start();
		
		$property = $step->addComponent("status", new WRadioList());
		$property->addOption('published', _("Published"));
		$property->addOption('draft', _("Draft"));
		$property->addOption('date', _("Published during period _____ to _______"));
		
// 		if ($component) {
// 			$val = $component->showDisplayNames();
// 			if ($val === true)
// 				$property->setValue('true');
// 			else if ($val === false)
// 				$property->setValue('false');
// 		}
		

		print "\n<strong>"._("Status:")."</strong>";
		print "\n<br/>[[status]]";
		
		
// 		$property = $step->addComponent("description", WTextArea::withRowsAndColumns(4,80));
// 		if ($component)
// 			$property->setValue($component->getDescription());
// 		print "\n<h2>"._("Description")."</h2>";
// 		print "\n"._("The Description for this content: ");
// 		print "\n<br />[[description]]";
// 		print "\n<div style='width: 400px'> &nbsp; </div>";
		
		
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		return $step;
	}
	
	/**
	 * save the status step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/9/07
	 */
	function saveStatusStep ($values) {
		return true;
	}
	
	/**
	 * Print sort order controls for flow organizers.
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printSortMethod ( $siteComponent, $step ) {
		$property = $step->addComponent("sort_method", new WSelectList());
		$methods = array(
			'default' => _('use default'),
			'custom' => _('Override - Custom'), 
			'title_asc' => _('Override - Alphabetic by Title - Ascending'), 
			'title_desc' => _('Override - Alphabetic by Title - Descending'),
			'create_date_asc' => _("Override - Chronologically by Create Date - Ascending"),
			'create_date_desc' => _("Override - Chronologically by Create Date - Descending"),
			'mod_date_asc' => _("Override - Chronologically by Modification Date - Ascending"),
			'mod_date_desc' => _("Override - Chronologically by Modification Date - Descending"));
		foreach ($methods as $method => $display)
			$property->addOption($method, $display);
		
		if ($siteComponent) {
			$property->setValue($siteComponent->sortMethodSetting());			
			$parent = $siteComponent->getParentComponent();
		} else {
			$parent = null;
		}
		

		print "\n<p><strong>"._("Content Sort Method:")."</strong> ";
		print "\n[[sort_method]]";
		
		if ($parent) {
			print "\n<br/>".str_replace('%1', 
				$parent->sortMethod(),
				_("Current default setting: %1"));
		}
		print "\n<br/><span style='font-size: smaller;'>";
		print _("This setting will change how 'content containers' sort their content. The 'custom' setting allows manual arrangement.");
		print "</span>";
		print "\n</p>";		
	}
	
	/**
	 * Print width controls
	 * 
	 * @param SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 4/17/06
	 */
	function printWidth ( $siteComponent, $step ) {
		$property = $step->addComponent('width', new WTextField);
		$property->setValue($siteComponent->getWidth());
		$property->setSize(6);
		$property->setErrorRule(new WECRegex("^([0-9]+(px|%))?$"));
		$property->setErrorText(_("Must be blank or in either pixel or percent form; e.g. '150px', 200px', '100%', '50%', etc."));
		
		print "<div style='font-weight: bold;'>"._('Maximum Width Guideline: ');
		print "[[width]]";
		print "</div>";
		print "<div style='font-size: smaller;'>"
			._("If desired, enter a width in either pixel or percent form; e.g. '150px', 200px', '100%', '50%', etc.<br/><strong>Note:</strong> This width is a guideline and is not guarenteed to be enforced. Content will fill the page, using this guideline where possible. Content inside of this container may stretch it beyond the specified width.")."</div>";		
	}
	
	/**
	 * Save the width results
	 * 
	 * @param object SiteComponent $component
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/15/07
	 */
	function saveWidth ( $component, $values ) {
		$component->updateWidth($values['width']);
		return true;
	}
	
	/**
	 * Save the sort method results
	 * 
	 * @param object SiteComponent $component
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/15/07
	 */
	function saveSortMethod ( $component, $values ) {
		$component->updateSortMethodSetting($values['sort_method']);
		return true;
	}
	
	/**
	 * Answer a step for changing permissions.
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 11/1/07
	 */
	public function getPermissionsStep () {
		$step =  new WizardStep();
		$step->setDisplayName(_("Permissions"));
		$property = $step->addComponent("perms_table", new RowRadioMatrix);
		
		$roleMgr = SegueRoleManager::instance();
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		$component = $this->getSiteComponent();
		$componentId = $idMgr->getId($component->getId());
		
		
		// Add the options
		foreach($roleMgr->getRoles() as $role)
			$property->addOption($role->getIdString(), $role->getDisplayName(), $role->getDescription());
		
		// Make the whole property read-only if we can view but not modify authorizations
		if (!$authZ->isUserAuthorized(
				$idMgr->getId("edu.middlebury.authorization.modify_authorizations"),
				$componentId))
		{
			$property->setEnabled(false);
		}
		
		// Get a list of the parent components.
		$parents = array();
		$parent = $component->getParentComponent();
		while ($parent) {
			if ($parent->acceptVisitor(new IsAuthorizableVisitor))
				$parents[] = $parent;
			$parent = $parent->getParentComponent();
		}
		
		$tabs = "";
		for ($i = count($parents) - 1; $i >= 0; $i--) {
			$parent = $parents[$i];
			$parentId = $idMgr->getId($parent->getId());
			
			// Everyone
			$agentId = $idMgr->getId('edu.middlebury.agents.everyone');
			$everyoneRole = $roleMgr->getAgentsRole($agentId, $parentId)->getIdString();
			$property->addField("everyone_".$parent->getId(), $tabs._("The World"), $everyoneRole);
			
			$property->addSpacerBefore("<br/>".$tabs.$parent->getDisplayName());
			
			// @todo This should be edu.middlebury.agents.institute
			$agentId = $idMgr->getId('edu.middlebury.institute');
			$agentMgr = Services::getService("Agent");
			$agent = $agentMgr->getGroup($agentId);
			$instituteRole = $roleMgr->getAgentsRole($agentId, $parentId)->getIdString();
			$property->addField("institute_".$parent->getId(), $tabs.$agent->getDisplayName(), $instituteRole);
			
			// Disable changing of parent roles
			foreach($roleMgr->getRoles() as $role) {
				$property->makeDisabled('everyone_'.$parent->getId(), $role->getIdString());
				$property->makeDisabled('institute_'.$parent->getId(), $role->getIdString());
			}
			
			$tabs .= " &nbsp; &nbsp;";
// 			$property->addSpacer();
		}
		
		// Everyone
		$agentId = $idMgr->getId('edu.middlebury.agents.everyone');	
		$property->addField("everyone", $tabs._("The World"), 
			$roleMgr->getAgentsRole($agentId, $componentId)->getIdString());
		
		$property->addSpacerBefore("<br/>".$tabs.$component->getDisplayName());
		
		// Disable all options up to the max parent role.
		foreach ($property->getOptions() as $option) {
			if ($option->value == $everyoneRole)
				break;
			else
				$property->makeDisabled('everyone', $option->value);
		}
		
		//Institute
		$agentId = $idMgr->getId('edu.middlebury.institute');
		$agentMgr = Services::getService("Agent");
		$agent = $agentMgr->getGroup($agentId);
		$property->addField("institute", $tabs.$agent->getDisplayName(), 
			$roleMgr->getAgentsRole($agentId, $componentId)->getIdString(), 
			">=");
		
		// Disable all options up to the max parent role.
		foreach ($property->getOptions() as $option) {
			if ($option->value == $instituteRole)
				break;
			else
				$property->makeDisabled('institute', $option->value);
		}
		
		// Make the everyone and institute groups unable to be given adminstrator privelidges
		$property->makeDisabled('everyone', 'admin');
		$property->makeDisabled('institute', 'admin');
		

// 		$property->addSpacer();
// 		$property->addField("private", _("All Faculty"), 'no_access');
		
		
		ob_start();
		print "\n<h2>"._("Permissions")."</h2>";
		print "\n<p>";
		print _("Here you can set permissions for this component and its children. Permissions are additive -- this means that you can add additional permissions (but not remove them) for any children.");
		print "\n</p>\n";
		print "[[perms_table]]";
		
		$step->setContent(ob_get_clean());
		
		return $step;
	}
	
	/**
	 * Save the permissions
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 11/5/07
	 */
	public function savePermissionsStep (array $values) {
		$roles = $values['perms_table'];
		
		$roleMgr = SegueRoleManager::instance();
		$idMgr = Services::getService("Id");
		
		$component = $this->getSiteComponent();
		$componentId = $idMgr->getId($component->getId());
		
		$everyoneId = $idMgr->getId('edu.middlebury.agents.everyone');
		$instituteId = $idMgr->getId('edu.middlebury.institute');
		
		$everyoneRole = $roleMgr->getRole($roles['everyone']);
		// Ensure that Everyone is not set to admin
		if ($everyoneRole->getIdString() == 'admin')
			$everyoneRole = $roleMgr->getRole('editor');
		
		$instituteRole = $roleMgr->getRole($roles['institute']);
		// Ensure that Institute is not set to admin
		if ($instituteRole->getIdString() == 'admin')
			$instituteRole = $roleMgr->getRole('editor');
		
		// Find the parent node.
		$parent = $component->getParentComponent();
		$parentQualifierId = $parent->getQualifierId();
		$parentEveryoneRole = $roleMgr->getAgentsRole($everyoneId, $parentQualifierId);
		$parentInstituteRole = $roleMgr->getAgentsRole($instituteId, $parentQualifierId);
		
		// Apply the Everyone Role.
		try {
			if ($everyoneRole->isEqualTo($parentEveryoneRole)) {
				$roleMgr->clearRoleAZs($everyoneId, $componentId);
			} else {
				$everyoneRole->apply($everyoneId, $componentId);
			}
			
			// If the roles are equal, clear out the explicit institute AZs
			// as institute users will get implicit AZs from Everyone
			if ($instituteRole->isEqualTo($everyoneRole) || $instituteRole->isEqualTo($parentInstituteRole)) {
				$roleMgr->clearRoleAZs($instituteId, $componentId);
			} else {
				$instituteRole->apply($instituteId, $componentId);
			}
		} catch (PermissionDeniedException $e) {
		
		}
		
		return true;
	}
}

?>