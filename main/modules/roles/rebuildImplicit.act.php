<?php
/**
 * @since 7/10/08
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This action will rebuild all implicit authorizations in a site. This shouldn't
 * be needed, but will help fix things up if there are problems.
 * 
 * @since 7/10/08
 * @package segue.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class rebuildImplicitAction
	extends Action
{
		
	/**
	 * Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/10/08
	 */
	public function isAuthorizedToExecute () {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");

		return $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"),
				$idManager->getId("edu.middlebury.authorization.root"));
	}
	
	/**
	 * Execute the action
	 * 
	 * @return void
	 * @access public
	 * @since 7/10/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException();
		
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		$hierarchyManager = Services::getService('Hierarchy');
		
		$site = SiteDispatcher::getCurrentRootNode();
		$hierarchy = $hierarchyManager->getHierarchy(
			$idManager->getId("edu.middlebury.authorization.hierarchy"));
		$infoList = $hierarchy->traverse(
			$idManager->getId($site->getId()),
			Hierarchy::TRAVERSE_MODE_DEPTH_FIRST,
			Hierarchy::TRAVERSE_DIRECTION_DOWN,
			Hierarchy::TRAVERSE_LEVELS_ALL);
		
		$status = new StatusStars(str_replace('%1', $infoList->count(), _("Rebuilding Implicit AZs on %1 nodes.")));
		$status->initializeStatistics($infoList->count());
		$azCache = $authZ->getAuthorizationCache();
		while ($infoList->hasNext()) {
			$info = $infoList->next();
			$node = $hierarchy->getNode($info->getNodeId());
// 			printpre("Rebuilding implicit AZs for ".$node->getId()." '".$node->getDisplayName()."'. Ancestors:");
// 			printpre($node->getAncestorIds());
			$azCache->createHierarchyImplictAZs($node, $node->getAncestorIds());
			$status->updateStatistics();
		}
		
		printpre("Done.");
		
		/*********************************************************
		 * Log the event
		 *********************************************************/
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Error",
							"Errors that did not halt execution");
			
			
			$item = new AgentNodeEntryItem("Rebuilt Implict AZs", "Hierarchy-Implicit AZs for site '".$site->getDisplayName()."' were rebuilt manually.");
			
			$item->addNodeId($site->getQualifierId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
	}
	
}

?>