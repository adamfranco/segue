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
				
		// Roles Step.
		$this->addRolesStep($wizard);
		
		// Template
		$this->addTemplateStep($wizard);
		
		// Theme
		$wizard->addStep("theme", $this->getThemeStep());
		$wizard->makeStepRequired('theme');
		
		return $wizard;
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
	 * Add a step to set site-wide permissions.
	 * 
	 * @param object Wizard $wizard
	 * @return void
	 * @access protected
	 * @since 8/13/08
	 */
	protected function addRolesStep (Wizard $wizard) {
		$step = new WizardStep();
		$step->setDisplayName(_("Roles"));	
		
		$rolesProperty = $step->addComponent('roles', new RowRadioMatrix);
		$roleMgr = SegueRoleManager::instance();
		// Add the options
		foreach($roleMgr->getRoles() as $role) {
			if (!$role->isEqual($roleMgr->getRole('custom')))
				$rolesProperty->addOption($role->getIdString(), $role->getDisplayName(), $role->getDescription());
		}
		
		// Add agents.
		$agentMgr = Services::getService("Agent");
		$idMgr = Services::getService("Id");
		
		// Super groups
		$agentsIds = array();
		$agentsIds[] = $idMgr->getId('edu.middlebury.agents.everyone');
		$agentsIds[] = $idMgr->getId('edu.middlebury.institute');
		
		foreach ($agentsIds as $agentId) {
			$agent = $agentMgr->getAgentOrGroup($agentId);
			$rolesProperty->addField($agentId->getIdString(), $agent->getDisplayName(), 'no_access');
		}
		
		$membersProperty = $step->addComponent('site_members', new MembershipButton($this->getSlot()->getShortname()));
		
		ob_start();
		print _("Site-Members");
		print " [[site_members]]";
		print "\n<div style='font-size: smaller; font-weight: normal; width: 300px;'>";
		print _("This is a custom group of users that are associated with this site. Users and groups can manually be made site-members or users can self-register using the 'Join Site' plugin if it is enabled.");
		print "</div>";
		$rolesProperty->addField('edu.middlebury.site-members.temp', ob_get_clean(), 'commenter');
		
		// Other owners
		foreach ($this->getOwners() as $agentId) {
			$agent = $agentMgr->getAgentOrGroup($agentId);
			$rolesProperty->addField($agentId->getIdString(), $agent->getDisplayName(), 'admin');
		}
		
		$rolesProperty->makeDisabled('edu.middlebury.agents.everyone', 'admin');
		$rolesProperty->makeDisabled('edu.middlebury.institute', 'admin');
		
		// Search
		$property = $step->addComponent("search", new AddSiteAgentSearchField);
		$property->setRolesProperty($rolesProperty);
		
		
		
		// Create the step text
		ob_start();
		print "\n<h2>"._("Site-wide Roles")."</h2>";
		print "\n<p>"._("Below you can set site-wide roles for users and groups over the entire site. Once the site is created you can use the <strong>Roles</strong> button (at the top of the page) to set the roles that users and groups have on various parts of the site.")."</p>";
		print "\n<p><strong>"._("Roles are always additive:")."</strong></p> <ul><li>"._("The Commenter role includes the Reader role, and the Author role is a superset of the Reader and Commenter roles. Click on the role-headings for more details.")."</li><li>"._("Groups and individuals can later be given additional roles on particular sections or pages of the site, but site-wide roles can not reduced on particular sections or pages.")."</li></ul>";
		print "\n[[roles]]";
		print "\n<p>"._("Search for users or groups:")."[[search]]";
		print "\n<br/>"._("<em>If you wish to give a role to a class, search for its course code, for example: </em> <code>span0101a-f08</code>");
		print "</p>";
		print "\n<div style='width: 400px'> &nbsp; </div>";
		$step->setContent(ob_get_clean());
		
		$step = $wizard->addStep("roles", $step);
		$wizard->makeStepRequired('roles');
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
		
		print "\n<p>"._("Templates are site 'starting points'. Each template provides you with a different starting set of sections and pages to help you get started. These pages can be renamed or deleted and new pages can always be added.")."</p>";
		
		$property = $step->addComponent('template', new WRadioList);
		$templateMgr = Segue_Templates_TemplateManager::instance();
		$templates = $templateMgr->getTemplates();
		if (!count($templates))
			throw new OperationFailedException("No templates available.");
		$property->setValue($templates[0]->getIdString());
		foreach ($templates as $template) {
			ob_start();
			try {
				$thumb = $template->getThumbnail();
				$harmoni = Harmoni::instance();
				$url = $harmoni->request->quickUrl('templates', 'template_thumbnail', array('template' => $template->getIdString()));
				print "\n\t<img src='".$url."' style='float: left; width: 200px; margin-right: 10px;' alt='"._('Template thumbnail')."' ";
				print " onclick=\"";
				print "var templatePreview = window.open('$url', 'template_preview', 'toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500'); ";
				print "templatePreview.focus();";
				print "\" ";
				print "/>";
			} catch (UnimplementedException $e) {
				print "\n\t<div style='font-style: italic'>"._("Thumbnail not available.")."</div>";
			} catch (OperationFailedException $e) {
// 				print "\n\t<div style='font-style: italic'>"._("Thumbnail not available.")."</div>";
			}
			print "\n\t<p>".$template->getDescription()."</p>";
			
			print "\n\t<div style='clear: both;'> &nbsp; </div>";
			$property->addOption($template->getIdString(), "<strong>".$template->getDisplayName()."</strong>", ob_get_clean());
		}
		
		print '[[template]]';
		
		$step->setContent(ob_get_contents());
		ob_end_clean();
		
		$step = $wizard->addStep("template_step", $step);
		$wizard->makeStepRequired('template_step');
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
		 * Create the site from the template
		 *********************************************************/			
		$templateMgr = Segue_Templates_TemplateManager::instance();
		$template = $templateMgr->getTemplate($properties['template_step']['template']);
		$site = $template->createSite($this->getSlot(), 
			$properties['namedescstep']['display_name'],
			$properties['namedescstep']['description']);
		
		$this->_siteId = $site->getId();
		$siteId = $idManager->getId($site->getId());
		
		
		/*********************************************************
		 * // Check the Role of the creator and make sure it is 'admin'
		 *********************************************************/
		$roleMgr = SegueRoleManager::instance();
		$role = $roleMgr->getUsersRole($site->getQualifierId(), true);
		$admin = $roleMgr->getRole('admin');
		if ($role->isLessThan($admin))
			$admin->applyToUser($site->getQualifierId(), true);
			
		/*********************************************************
		 * Set site-wide roles for other users
		 *********************************************************/
		foreach ($properties['roles']['roles'] as $agentIdString => $roleId) {
			if ($agentIdString == 'edu.middlebury.site-members.temp') {
				$agentId = $site->getMembersGroup()->getId();
			} else {
				$agentId = $idManager->getId($agentIdString);
			}
			$role = $roleMgr->getRole($roleId);
			$role->apply($agentId, $site->getQualifierId());
		}
		
		/*********************************************************
		 * // Check the Role again of the creator and make sure it is 'admin'
		 *********************************************************/
		$roleMgr = SegueRoleManager::instance();
		$role = $roleMgr->getUsersRole($site->getQualifierId(), true);
		$admin = $roleMgr->getRole('admin');
		if ($role->isLessThan($admin))
			$admin->applyToUser($site->getQualifierId(), true);
		
		
		/*********************************************************
		 * Add any specified users to the site-members group.
		 *********************************************************/
		$members = $properties['roles']['site_members'];
		$membersGroup = $site->getMembersGroup();
		$agentMgr = Services::getService('Agent');
		foreach ($members as $idString => $name) {
			$membersGroup->add($agentMgr->getAgentOrGroup($idManager->getId($idString)));
		}
		
		/*********************************************************
		 * Theme
		 *********************************************************/
		$this->saveThemeStep($properties['theme'], $site);
		
		/*********************************************************
		 * Log the success or failure
		 *********************************************************/
			$slot = $this->getSlot();
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
		if (isset($this->_siteId) && $this->_siteId) 
			return SiteDispatcher::quickURL($harmoni->request->getRequestedModule(), "editview", array(
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
	
	/**
	 * Answer the theme step
	 * 
	 * @param 
	 * @return object WizardStep
	 * @access protected
	 * @since 8/13/08
	 */
	protected function getThemeStep () {
		
		$step =  new WizardStep();
		$step->setDisplayName(_("Theme"));
		$property = $step->addComponent("theme", new WRadioListWithDelete);
		
		$themeMgr = Services::getService("GUIManager");
		foreach ($themeMgr->getThemeSources() as $source) {
			try {
				
				foreach ($source->getThemes() as $theme) {
					ob_start();
					try {
					
						try {
							$thumb = $theme->getThumbnail();
							$harmoni = Harmoni::instance();
							print "\n\t<img src='".$harmoni->request->quickUrl('gui2', 'theme_thumbnail', array('theme' => $theme->getIdString()))."' style='float: left; width: 200px; margin-right: 10px;'/>";
						} catch (UnimplementedException $e) {
							print "\n\t<div style='font-style: italic'>"._("Thumbnail not available.")."</div>";
						} catch (OperationFailedException $e) {
							print "\n\t<div style='font-style: italic'>"._("Thumbnail not available.")."</div>";
						}
						print "\n\t<p>".$theme->getDescription()."</p>";
						
						// Delete Theme
						if ($theme->supportsModification()) {
							$modSess = $theme->getModificationSession();
							if ($modSess->canModify()) {
								$allowDelete = true;
							} else {
								$allowDelete = false;
							}
						} else {
							$allowDelete = false;
						}
						
						print "\n\t<div style='clear: both;'> &nbsp; </div>";
						$property->addOption($theme->getIdString(), "<strong>".$theme->getDisplayName()."</strong>", ob_get_contents(), $allowDelete);
					} catch (Exception $e) {
					}
					ob_end_clean();
				} 
			} catch (Exception $e) {
			}
		}
		
		if (defined('SEGUE_DEFAULT_SITE_THEME'))
			$property->setValue(SEGUE_DEFAULT_SITE_THEME);
		else
			$property->setValue($themeMgr->getDefaultTheme()->getIdString());
		
		ob_start();
		print "\n<h2>"._("Theme")."</h2>";
		print "\n<p>";
		print _("Here you can set the theme for the site. The theme is the 'look and feel' of your site. Most themes allow you to change the text and background colors once the site has been created. You can change your site's theme at any time in the future."); 
		print "\n</p>\n";
		print "[[theme]]";
		
		$step->setContent(ob_get_clean());
		
		return $step;
	}
	
	/**
	 * Save the theme step
	 * 
	 * @param array $values
	 * @param object SiteNavBlockSiteComponent $site
	 * @return boolean
	 * @access protected
	 * @since 5/8/08
	 */
	protected function saveThemeStep (array $values, SiteNavBlockSiteComponent $site) {
		$themeMgr = Services::getService("GUIManager");
		
		/*********************************************************
		 * Set the default theme of the site.
		 *********************************************************/
		$themeMgr = Services::getService('GUIManager');
		try {		
			// Set the chosen theme.
			if (is_null($values['theme']['selected'])) {
				if (defined('SEGUE_DEFAULT_SITE_THEME'))
					$site->updateTheme($themeMgr->getTheme(SEGUE_DEFAULT_SITE_THEME));
				else
					$site->updateTheme($themeMgr->getDefaultTheme());
			} else {
				$site->updateTheme($themeMgr->getTheme($values['theme']['selected']));
			}
		
		} catch (UnknownIdException $e) {
			$site->updateTheme($themeMgr->getDefaultTheme());
		}
		
		return true;
	}
}

?>