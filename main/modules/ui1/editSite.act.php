<?php
/**
 * @since 5/8/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editSite.act.php,v 1.5 2007/11/07 19:00:54 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueClassicWizard.abstract.php");
require_once(dirname(__FILE__)."/Rendering/EditModeSiteVisitor.class.php");

/**
 * This action provides a wizard for editing a navigation node
 * 
 * @since 5/11/07
 * @package segue.modules.classic_ui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: editSite.act.php,v 1.5 2007/11/07 19:00:54 adamfranco Exp $
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
			if (!$this->saveStatusStep($properties['status']))
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
		
		try {
			$visitor = new EditModeSiteVisitor;
			try {
				$headerId = $visitor->getHeaderId($component);
			} 
			// If we don't have a header, see if we have an empty header cell.
			catch (Exception $e) {
				$headerCellId = $visitor->getHeaderCellId($component);
			}
			
			
			$harmoni = Harmoni::instance();
			print "<iframe src='".$harmoni->request->quickURL('ui1', 'editHeader')."' height='800px' width='100%' />";
		} catch (Exception $e) {
			print _("This site is configured in a way that does not have a site header. A header can be added using the <em>New Mode</em> user interface.");
		}
		
		$step->setContent(ob_get_clean());
		
		return $step;
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
		$step->setDisplayName(_("Site-Wide Permissions"));
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
		// @todo This should be edu.middlebury.agents.institute
		$instituteId = $idMgr->getId('edu.middlebury.agents.users');
		
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
}

?>