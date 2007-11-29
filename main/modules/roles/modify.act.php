<?php
/**
 * @since 11/14/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: modify.act.php,v 1.6 2007/11/29 20:21:53 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/Rendering/IsAuthorizableVisitor.class.php");
require_once(dirname(__FILE__)."/RoleAction.class.php");
require_once(dirname(__FILE__)."/Visitors/PopulateRolesVisitor.class.php");


/**
 * An action for editing permissions of a particular site
 * 
 * @since 11/14/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: modify.act.php,v 1.6 2007/11/29 20:21:53 adamfranco Exp $
 */
class modifyAction
	extends RoleAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/14/07
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/14/07
	 */
	function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough("node");
		$harmoni->request->passthrough("agent");
		$harmoni->request->passthrough("returnNode");
		$harmoni->request->passthrough("returnModule");
		$harmoni->request->passthrough("returnAction");
		
		$centerPane = $this->getActionRows();
		$qualifierId = $this->getSiteId();
		$this->cacheName = get_class($this).'_'.$qualifierId->getIdString();
		
		$this->runWizard ( $this->cacheName, $centerPane );
	}
	
	/**
	 * Create the wizard
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 11/14/07
	 */
	public function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = SingleStepWizard::withText(
				"<div>\n" .
				"<table width='100%' border='0' cellpadding='0' cellspacing='2'>\n" .
				"<tr>\n" .
				"<td align='left' width='50%'>\n" .
				"[[save_and_choose]]\n" .
				"<br/>[[cancel_and_choose]]\n" .
				"</td>\n" .
				"<td align='right' width='50%'>\n" .
				"[[_save]]\n" .
				"<br/>[[_cancel]]\n" .
				"</td></tr></table>" .
				"</div>\n" .
				"<hr/>\n" .
				"<div>\n" .
				"[[_steps]]" .
				"</div>\n");
		
		$wizard->addComponent("choose_user", new ButtonPressedListener("edu.middlebury.segue.choose_user"));
		
		$button = $wizard->addComponent("save_and_choose", WSaveButton::withLabel("<< "._("Save and Choose User")));
		$button->addEvent("edu.middlebury.segue.choose_user");
		
		$button = $wizard->addComponent("cancel_and_choose", WCancelButton::withLabel("<< "._("Cancel and Choose User")));
		$button->addEvent("edu.middlebury.segue.choose_user");
		
		$step = $wizard->addStep("permissions", new WizardStep);
		$property = $step->addComponent("perms_table", new RowHierarchicalRadioMatrix);
		
		
		$agent = $this->getAgent();
		
		if ($agent->isGroup())
			$type = _("group");
		else
			$type = _("user");
		$title = str_replace("%1", $type,
					str_replace ("%2", $agent->getDisplayName(),
						_("Permissions for %1 '%2'")));		
		
		
		$roleMgr = SegueRoleManager::instance();
		// Add the options
		foreach($roleMgr->getRoles() as $role)
			$property->addOption($role->getIdString(), $role->getDisplayName(), $role->getDescription());
		
		$this->getSite()->acceptVisitor(new PopulateRolesVisitor($property, $agent));
		
		
		ob_start();
		print "\n<h2>".$title."</h2>";
		print "\n<p>";
		print _("Permissions are additive -- this means that you can add additional permissions (but not remove them) for child-nodes.");
		print "\n</p>\n";
		
		print "\n<p>";
		print _("Tip: Hold down the <em>SHIFT</em> key while clicking to revoke roles from child-nodes.");
		print "\n</p>\n";
		
		print "[[perms_table]]";
		
		$step->setContent(ob_get_clean());
		
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
			$roleFields = $properties['permissions']['perms_table'];
			
			$roleMgr = SegueRoleManager::instance();
			foreach ($roleFields as $componentId => $roleId) {
				$this->saveRole(
					$this->getSiteComponentForIdString($componentId), 
					$roleMgr->getRole($roleId));
			}
			
			return true;
		} else {
			return FALSE;
		}
	}
	
	/**
	 * Save a role for a hierarchy node
	 * 
	 * @param object SiteComponent $component
	 * @param object SegueRole $role
	 * @return <##>
	 * @access public
	 * @since 11/16/07
	 */
	public function saveRole (SiteComponent $component, SegueRole $role) {
		$roleMgr = SegueRoleManager::instance();
		$idMgr = Services::getService("Id");
		
		$agentId = $this->getAgentId();
		$componentId = $idMgr->getId($component->getId());
		
		
		// Ensure that Everyone or Institute are not set to admin
		$everyoneId = $idMgr->getId('edu.middlebury.agents.everyone');
		$instituteId = $idMgr->getId('edu.middlebury.institute');
		if ($agentId->isEqual($everyoneId) || $agentId->isEqual($instituteId)) {
			if ($role->getIdString() == 'admin')
				$role = $roleMgr->getRole('editor');
		}
		
// 		printpre("Saving role '".$role->getIdString()."' for ".$agentId." at ".$component->getDisplayName());
		
		// Find the parent node.
		$parent = $component->getParentComponent();
		if ($parent) {
			$parentQualifierId = $parent->getQualifierId();
			$parentRole = $roleMgr->getAgentsRole($agentId, $parentQualifierId, true);
		}
		
		// Apply the role or clear it if it is less than the implicitly given role.
		try {
			if (isset($parentRole) && $role->isLessThanOrEqualTo($parentRole)) {
				$roleMgr->clearRoleAZs($agentId, $componentId);
// 				printpre("Clearing duplicate role '".$role->getIdString()."' for ".$agentId." at ".$component->getDisplayName());
			} else {
				$role->apply($agentId, $componentId);
			}
		} catch (PermissionDeniedException $e) {
		
		}
		
		return true;
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 11/14/07
	 */
	function getReturnUrl () {
		$wizard = $this->getWizard($this->cacheName);
		
		$harmoni = Harmoni::instance();
		
		$chooseUserListener = $wizard->getChild('choose_user');
		if ($chooseUserListener->wasPressed())
			return $harmoni->request->quickURL('roles', 'choose_agent');
		else {
			if (RequestContext::value('returnModule'))
				$module = RequestContext::value('returnModule');
			else
				$module = 'ui1';
			
			if (RequestContext::value('returnAction'))
				$action = RequestContext::value('returnAction');
			else
				$action = 'editview';
			return $harmoni->request->quickURL($module, $action);
		}
	}
	
	/**
	 * Answer the AgentId
	 * 
	 * @return object Id
	 * @access public
	 * @since 11/15/07
	 */
	public function getAgentId () {
		$idManager = Services::getService("Id");
		
		if (RequestContext::value("agent"))
			return $idManager->getId(RequestContext::value("agent"));
		else
			throw new Exception("No AgentId specified.");
	}
	
	/**
	 * Answer the chosen Agent
	 * 
	 * @return object Agent
	 * @access public
	 * @since 11/15/07
	 */
	public function getAgent () {
		$agentManager = Services::getService("Agent");
		return $agentManager->getAgentOrGroup($this->getAgentId());
	}	
}

?>