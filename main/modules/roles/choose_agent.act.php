<?php
/**
 * @since 11/15/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: choose_agent.act.php,v 1.4 2007/11/29 20:25:33 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/RoleAction.class.php");

/**
 * An action for editing permissions of a particular site
 * 
 * @since 11/14/07
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: choose_agent.act.php,v 1.4 2007/11/29 20:25:33 adamfranco Exp $
 */
class choose_agentAction
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
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		return $authZ->isUserAuthorizedBelow(
				$idManager->getId("edu.middlebury.authorization.view_authorizations"), 
				$this->getSiteId());
			
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
		$cacheName = get_class($this).'_'.$qualifierId->getIdString();
		
		$this->runWizard ( $cacheName, $centerPane );
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
				"[[_cancel]]\n" .
				"</td>\n" .
				"<td align='right' width='50%'>\n" .
				"</td></tr></table>" .
				"</div>\n" .
				"<hr/>\n" .
				"<div>\n" .
				"[[_steps]]" .
				"</div>\n");
		
		$step = $wizard->addStep("agents", new WizardStep);
		
		ob_start();
		print "\n<h2>"._("Permissions")."</h2>";
		print "\n<p>";
		print _("Choose a user or group to edit permissions for.");
		print "\n</p>\n";
		
		
		$agentMgr = Services::getService("Agent");
		$idMgr = Services::getService("Id");
		$harmoni = Harmoni::instance();
		$roleMgr = SegueRoleManager::instance();
		
		$everyoneId = $idMgr->getId("edu.middlebury.agents.everyone");
		$instituteId = $idMgr->getId("edu.middlebury.institute");
		
		$agents = array();
		$agents[] = $agentMgr->getGroup($everyoneId);
		$agents[] = $agentMgr->getGroup($instituteId);
		
		$agentIdsWithRoles = $roleMgr->getAgentsWithRoleAtLeast($roleMgr->getRole('reader'), $this->getSiteId(), true);
		foreach ($agentIdsWithRoles as $id) {
			if (!$id->isEqual($everyoneId) && !$id->isEqual($instituteId))
				$agents[] = $agentMgr->getAgentOrGroup($id);
		}
		
		print "\n<table width='100%' class='search_results' cellspacing='0'>";
		$i = 0;
		foreach ($agents as $agent) {
			print "\n\t<tr class='search_result_item'>";
			print "\n\t\t<td class='color$i'>";
			print "\n\t\t\t".$agent->getDisplayName();
			print "\n\t\t</td>";
			print "\n\t\t<td class='color$i' style='text-align: right;'>";
			print "\n\t\t\t<a href='";
			print $harmoni->request->quickURL('roles', 'modify', array(
				'node' => RequestContext::value('node'),
				'agent' => $agent->getId()->getIdString()
			));
			print "'><input type='button' value=\""._("Modify Roles >>")."\"/></a>";
			print "\n\t\t</td>";
			print "\n\t</tr>";
			$i = intval(!$i);
		}
		print "\n</table>";
		
		$property = $step->addComponent("search", new WSearchField);
		$property->setSearchSource(new AgentSearchSource);
		
		print "\n<div style='margin-top: 20px; border-top: 1px solid; padding: 5px;'>";
		print _("Search: ")." [[search]]";
		print "</div>";
		
		$step->setContent(ob_get_clean());
	
		return $wizard;
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 11/14/07
	 */
	function getReturnUrl () {		
		$harmoni = Harmoni::instance();

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

?>