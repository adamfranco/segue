<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editSite.act.php,v 1.9 2008/04/13 18:43:01 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");
require_once(dirname(__FILE__)."/Rendering/EditModeSiteVisitor.class.php");
require_once(MYDIR."/main/library/SiteDisplay/Rendering/HasHeaderFooterSiteVisitor.class.php");

/**
 * This action provides a wizard for editing a navigation node
 * 
 * @since 5/11/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editSite.act.php,v 1.9 2008/04/13 18:43:01 adamfranco Exp $
 */
class editSiteAction
	extends SegueClassicWizard
{
		
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 5/11/07
	 */
	function getHeadingText () {
		return _("Edit Site");
	}
	
	
	
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 9/24/07
	 */
	function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleStepWizard::withDefaultLayout();
		
		$wizard->addStep("namedesc", $this->getTitleStep());
		try {
			$wizard->addStep("permissions", $this->getPermissionsStep());
		} catch (PermissionDeniedException $e) {
		
		}
		$wizard->addStep("theme", $this->getThemeStep());
		$wizard->addStep("display", $this->getDisplayOptionsStep());
		$wizard->addStep("header", $this->getHeaderStep());
		
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
				
			if (!$this->saveThemeStep($properties['theme']))
				return FALSE;
			
			return TRUE;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Answer a step for editing the header.
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 9/24/07
	 */
	public function getHeaderStep () {
		$component = $this->getSiteComponent();
		
		$step = new WizardStep();
		$step->setDisplayName(_("Site Header and Footer"));
		
		// Create the step text
		ob_start();
		
		if ($component->acceptVisitor(new HasHeaderFooterSiteVisitor)) {
			$harmoni = Harmoni::instance();
			print "<iframe src='".$harmoni->request->quickURL('ui1', 'editHeader')."' height='800px' width='100%' />";
		} else {
			print _("This site is configured in a way that does not have a site header. A header can be added using the <em>New Mode</em> user interface.");
		}
		
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
		$property->addOption('true', _("show"));
		$property->addOption('false', _("hide"));
		$property->setValue('true');
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
		$property->addOption('true', _("enable"));
		$property->addOption('false', _("disable"));
		$property->setValue('false');
	}
	
	/**
	 * Answer a step for changing site-wide permissions.
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
		
		$siteId = $idMgr->getId($this->getSiteComponent()->getId());
		
		
		// Add the options
		foreach($roleMgr->getRoles() as $role)
			$property->addOption($role->getIdString(), $role->getDisplayName(), $role->getDescription());
		
		// Make the whole property read-only if we can view but not modify authorizations
		if (!$authZ->isUserAuthorized(
				$idMgr->getId("edu.middlebury.authorization.modify_authorizations"),
				$siteId))
		{
			$property->setEnabled(false);
		}
		
		
		// Everyone
		$agentId = $idMgr->getId('edu.middlebury.agents.everyone');		
		$property->addField("everyone", _("The World"), 
			$roleMgr->getAgentsExplicitRole($agentId, $siteId)->getIdString());
		
		// @todo This should be edu.middlebury.agents.institute
		$agentId = $idMgr->getId('edu.middlebury.institute');
		$agentMgr = Services::getService("Agent");
		$agent = $agentMgr->getGroup($agentId);
		$property->addField("institute", $agent->getDisplayName(), 
			$roleMgr->getAgentsRole($agentId, $siteId)->getIdString(), 
			">=");
		
		
		// Make the everyone and institute groups unable to be given adminstrator privelidges
		$property->makeDisabled('everyone', 'admin');
		$property->makeDisabled('institute', 'admin');
		

// 		$property->addSpacer();
// 		$property->addField("private", _("All Faculty"), 'no_access');
		
		
		ob_start();
		print "\n<h2>"._("Permissions")."</h2>";
		print "\n<p>";
		print _("Here you can set the permissions for the world and/or institute users over the entire site. Permissions are additive -- this means that you can add additional permissions (but not remove them) for any part of the site."); 
		print "\n</p>\n";
		print "\n<p style='font-weight: bold;'>";
		print _("To change permissions for other parts of the site or for other users or groups, please click the 'permissions' button at the bottom of the edit-view of the site."); 
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
		
		$siteId = $idMgr->getId($this->getSiteComponent()->getId());
		
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
		
		// Apply the Everyone Role.
		try {
			$everyoneRole->apply($everyoneId, $siteId);
			
			// If the roles are equal, clear out the explicit institute AZs
			// as institute users will get implicit AZs from Everyone
			if ($instituteRole->isEqualTo($everyoneRole)) {
				$roleMgr->clearRoleAZs($instituteId, $siteId);
			} else {
				$instituteRole->apply($instituteId, $siteId);
			}
		} catch (PermissionDeniedException $e) {
		
		}
		
		return true;
	}
	
	/**
	 * Create the step for adding the display options.
	 * 
	 * @return object WizardStep
	 * @access public
	 * @since 5/13/08
	 */
	function getDisplayOptionsStep () {
		$component = $this->getSiteComponent();
		$step = parent::getDisplayOptionsStep();
		
		ob_start();
		$this->printWidth($component, $step);
		
		$step->setContent($step->getContent().ob_get_clean());
		return $step;
	}
	
	/**
	 * save the display options step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access public
	 * @since 5/13/08
	 */
	function saveDisplayOptionsStep ($values) {
		if (!parent::saveDisplayOptionsStep($values)) {
			return false;
		}
		$component = $this->getSiteComponent();
		$this->saveWidth($component, $values);
		return true;
	}
	
	/**
	 * Answer the theme step
	 * 
	 * @return object WizardStep
	 * @access protected
	 * @since 5/8/08
	 */
	protected function getThemeStep () {
		$component = $this->getSiteComponent();
		$step =  new WizardStep();
		$step->setDisplayName(_("Theme"));
		$property = $step->addComponent("theme", new WRadioListWithDelete);
		$property->setValue($component->getTheme()->getIdString());
		
		$themeMgr = Services::getService("GUIManager");
		foreach ($themeMgr->getThemes() as $theme) {
			ob_start();
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
			$property->addOption($theme->getIdString(), "<strong>".$theme->getDisplayName()."</strong>", ob_get_clean(), $allowDelete);
		}
		
		ob_start();
		print "\n<h2>"._("Theme")."</h2>";
		print "\n<p>";
		print _("Here you can set the theme for the site."); 
		print "\n</p>\n";
		print "[[theme]]";
		
		$step->setContent(ob_get_clean());
		
		return $step;
	}
	
	/**
	 * Save the theme step
	 * 
	 * @param array $values
	 * @return boolean
	 * @access protected
	 * @since 5/8/08
	 */
	protected function saveThemeStep (array $values) {
		$component = $this->getSiteComponent();
		$currentTheme = $component->getTheme();
		
		$themeMgr = Services::getService("GUIManager");
		
		// Delete deleted themes
		foreach ($values['theme']['deleted'] as $themeId) {
			$theme = $themeMgr->getTheme($themeId);
			if ($theme->supportsModification()) {
				$modSess = $theme->getModificationSession();
				if ($modSess->canModify()) {
					$theme->delete();
				}
			}
		}
		
		// Set the chosen theme.
		if (is_null($values['theme']['selected']))
			$newTheme = $themeMgr->getDefaultTheme();
		else
			$newTheme = $themeMgr->getTheme($values['theme']['selected']);
		
		if ($newTheme->getIdString() != $currentTheme->getIdString()) {
			$component->updateTheme($newTheme);
		}
		
		return true;
	}
}

?>