<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: moveComponent.act.php,v 1.9 2007/11/09 22:57:41 adamfranco Exp $
 */ 

require_once(MYDIR."/main/library/SiteDisplay/EditModeSiteAction.act.php");
require_once(MYDIR."/main/library/Roles/SegueRoleManager.class.php");

/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: moveComponent.act.php,v 1.9 2007/11/09 22:57:41 adamfranco Exp $
 */
class moveComponentAction 
	extends EditModeSiteAction
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
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		$director = $this->getSiteDirector();
		
		$component = $director->getSiteComponentById(RequestContext::value('component'));
		$sourceQualifierId = $component->getParentComponent()->getQualifierId();
		
		$targetId = RequestContext::value('destination');
		preg_match("/^(.+)_cell:(.+)$/", $targetId, $matches);
		$targetOrgId = $matches[1];
		$destination = $director->getSiteComponentById($targetOrgId);
		$destQualifierId = $destination->getQualifierId();
		
		return (
			(	$authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.remove_children"),
					$sourceQualifierId)
				|| $authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.delete"),
					$component->getQualifierId())
			)
			&& $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$destQualifierId));
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getUnauthorizedMessage () {
		return _("You are not authorized to move this <em>Node</em> here.");
	}
	
	/**
	 * Process changes to the site components. This is the method that the various
	 * actions that modify the site should override.
	 * 
	 * @param object SiteDirector $director
	 * @return void
	 * @access public
	 * @since 4/14/06
	 */
	function processChanges ( SiteDirector $director ) {
		// Get the target organizer's Id & Cell
		$targetId = RequestContext::value('destination');
		preg_match("/^(.+)_cell:(.+)$/", $targetId, $matches);
		$targetOrgId = $matches[1];
		$targetCell = $matches[2];
		
		$component = $director->getSiteComponentById(RequestContext::value('component'));
		
		// Store the existing Role of the user. 
		$roleMgr = SegueRoleManager::instance();
		$oldRole = $roleMgr->getUsersRole($component->getQualifierId(), true);
		
		// If we are moving a navOrganizer, update the target of the menu
		if (preg_match('/^.*NavOrganizerSiteComponent$/i', get_class($component))) {
			$menuOrganizer = $component->getMenuOrganizer();
			$menuOrganizer->updateTargetId(RequestContext::value('destination'));
			return;
		} 
		// If we are moving a menu to a nav block, make the menu nested.
		else if (preg_match('/^.*MenuOrganizerSiteComponent$/i', get_class($component))) {
			$newOrganizer = $director->getSiteComponentById($targetOrgId);
			$currentComponentInCell = $newOrganizer->getSubcomponentForCell($targetCell);
			if (preg_match('/^.*NavBlockSiteComponent$/i', get_class($currentComponentInCell))) {
				$currentComponentInCell->makeNested($component);
				return;
			}
			
		}
		
// 		printpre("targetId: ".$targetId);
// 		printpre("targetOrgId: ".$targetOrgId);
// 		printpre("targetCell: ".$targetCell);
// 		printpre("componentId: ".RequestContext::value('component'));
		
		$filledTargetIds = $director->getFilledTargetIds($targetOrgId);
		
		$newOrganizer = $director->getSiteComponentById($targetOrgId);
		$oldCellId = $newOrganizer->putSubcomponentInCell($component, $targetCell);
		
// 		printpre("oldCellId: ".$oldCellId);

		// If the targetCell was a target for any menus, change their targets
		// to the cell just vacated by the component we swapped with
		if (in_array($targetId, $filledTargetIds)) {
			$menuIds = array_keys($filledTargetIds, $targetId);
			foreach ($menuIds as $menuId) {
				$menuOrganizer = $director->getSiteComponentById($menuId);
// 				printpre(get_class($menuOrganizer));
				
				$menuOrganizer->updateTargetId($oldCellId);
			}
		}
		
		// Update the new role if needed
		$newRole = $roleMgr->getUsersRole($component->getQualifierId(), true);
		if ($newRole->isLessThan($oldRole))
			$oldRole->applyToUser($component->getQualifierId(), true);
		
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
			
			
			$item = new AgentNodeEntryItem("Component Moved", $component->getComponentClass()." moved.");
			
			$item->addNodeId($component->getQualifierId());
			
			
			$site = $component->getDirector()->getRootSiteComponent($component->getId());
			if (!$component->getQualifierId()->isEqual($site->getQualifierId()))
				$item->addNodeId($site->getQualifierId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
// 		exit;
	}
}

?>