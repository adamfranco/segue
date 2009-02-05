<?php
/**
 * @since 10/20/08
 * @package segue.agent
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/modules/roles/AgentSearchSource.class.php");

/**
 * This action allows modification of site-membership
 * 
 * @since 10/20/08
 * @package segue.agent
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class modify_membersAction
	extends MainWindowAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 10/20/08
	 */
	function isAuthorizedToExecute () {
		// Check for authorization
 		$authZManager = Services::getService("AuthZ");
 		$idMgr = Services::getService("IdManager");
 		return $authZManager->isUserAuthorized(
 					$idMgr->getId("edu.middlebury.authorization.modify_authorizations"),
 					$this->getSite()->getQualifierId());
	}
	
	/**
	 * Answer the group to modify
	 * 
	 * @return object Group
	 * @access protected
	 * @since 10/20/08
	 */
	protected function getGroup () {
		if (!isset($this->_group)) {
			$this->_group = $this->getSite()->getMembersGroup();
		}
		
		return $this->_group;
	}
	
	/**
	 * Anser the site
	 * 
	 * @return object SiteNavBlockSiteComponent
	 * @access protected
	 * @since 2/5/09
	 */
	protected function getSite () {
		if (!isset($this->_site)) {
	 		$this->_site = SiteDispatcher::getCurrentRootNode();
		}
		
		return $this->_site;
	}
	
	/**
	 * Execute the Action
	 * 
	 * @return void
	 * @access public
	 * @since 10/20/08
	 */
	public function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->passthrough('site');
		$harmoni->request->passthrough('returnModule');
		$harmoni->request->passthrough('returnAction');
		
		$outputHandler = $harmoni->getOutputHandler();
		$outputHandler->setHead(
			$outputHandler->getHead()
			."\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/AgentInfoPanel.js'></script>");
		
		$this->runWizard (get_class($this).'_'.$this->getGroup()->getId()->getIdString(), $this->getActionRows());
	}
	
	/**
	 * Create the wizard
	 * 
	 * @return Wizard
	 * @access public
	 * @since 10/20/08
	 */
	public function createWizard () {
		$wizard = SingleStepWizard::withDefaultLayout();
		$step = $wizard->addStep("members_step", new WizardStep);
		
		$harmoni = Harmoni::instance();
							
		$property = $step->addComponent('members', new WSearchList);
		$property->setSearchSource(new AgentSearchSource);
		
		$agentMgr = Services::getService("Agent");
		
		$childGroups = $this->getGroup()->getGroups(false);
		while ($childGroups->hasNext()) {
			$property->addValue(new AgentSearchResult($childGroups->next()));
		}
		$members = $this->getGroup()->getMembers(false);
		while ($members->hasNext()) {
			$property->addValue(new AgentSearchResult($members->next()));
		}
		
		
		ob_start();
		
		print "\n<h2>"._("Edit Site Members")."</h2>";
			
		print "\n<p>";
		print _("Site members are users and/or groups of users who are affiliated with the site.");
		print "\n</p>\n<p>";
		print _("By adding users and groups to the list of site members, these users can all be given the same roles in one step. Additional roles can always be given to individual users or groups as well.");
		print "</p>";
		print "[[members]]";
		
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
	 * @since 10/20/08
	 */
	function saveWizard ( $cacheName ) {
		$wizard = $this->getWizard($cacheName);
		
		$values = $wizard->getAllValues();
		$changes = array();
		
		try {
			$idMgr = Services::getService("Id");
			$group = $this->getGroup();
			
			$oldMembers = array();
			$childGroups = $this->getGroup()->getGroups(false);
			while ($childGroups->hasNext()) {
				$oldMembers[] = $childGroups->next()->getId()->getIdString();
			}
			$members = $this->getGroup()->getMembers(false);
			while ($members->hasNext()) {
				$oldMembers[] = $members->next()->getId()->getIdString();
			}
			$newMembers = $values['members_step']['members'];
			
			$agentMgr = Services::getService("Agent");
			// Remove any needed existing Members
			foreach ($oldMembers as $idString) {
				if (!in_array($idString, $newMembers)) {
					$agent = $agentMgr->getAgentOrGroup($idMgr->getId($idString));
					$agentName = $agent->getDisplayName();
					
					$group->remove($agent);
					
					$changes[] = "Member $agentName ($idString) removed";
				}
			}
			
			// Add an needed new Members
			foreach ($newMembers as $idString) {
				if (!in_array($idString, $oldMembers)) {
					$agent = $agentMgr->getAgentOrGroup($idMgr->getId($idString));
					$agentName = $agent->getDisplayName();
					
					$group->add($agent);
					
					$changes[] = "Member $agentName ($idString) added";
				}
			}
			
		} catch (Exception $e) {
			print $e->getMessage();
			return false;
		}
		
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item = new AgentNodeEntryItem("Modify Group", "Site-membership group modified. <br/><br/>Changes: <br/> ".implode(",<br/> ", $changes));
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		return true;
	}
	
	/**
	 * Answer the return URL
	 * 
	 * @return string
	 * @access public
	 * @since 10/20/08
	 */
	public function getReturnUrl () {
		$harmoni = Harmoni::instance();

		if (RequestContext::value('returnModule'))
			$module = RequestContext::value('returnModule');
		else
			$module = 'roles';
		
		if (RequestContext::value('returnAction'))
			$action = RequestContext::value('returnAction');
		else
			$action = 'choose_agent';
			
		$harmoni->request->forget('returnAction');
		$harmoni->request->forget('returnModule');
		return SiteDispatcher::quickURL($module, $action);
	}
	
}

?>