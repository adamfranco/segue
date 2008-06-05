<?php
/**
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: deleteComponent.act.php,v 1.12 2008/04/09 21:12:03 adamfranco Exp $
 */ 

require_once(MYDIR."/main/modules/ui2/EditModeSiteAction.abstract.php");


/**
 * 
 * 
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: deleteComponent.act.php,v 1.12 2008/04/09 21:12:03 adamfranco Exp $
 */
class deleteComponentAction 
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
		$component = $director->getSiteComponentById(SiteDispatcher::getCurrentNodeId());
				
		return $authZ->isUserAuthorized(
			$idManager->getId("edu.middlebury.authorization.delete"),
			$component->getQualifierId());
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
		$component = $director->getSiteComponentById(SiteDispatcher::getCurrentNodeId());
		
		$this->findSafeReturnNode($director, $component);
		
		$organizer = $component->getParentComponent();
		if ($organizer)
			$organizer->detatchSubcomponent($component);

		$rootSiteComponent = $director->getRootSiteComponent(SiteDispatcher::getCurrentNodeId());
		// If we are deleting the site unhitch it from the slot
		if ($rootSiteComponent->getId() == SiteDispatcher::getCurrentNodeId()) {
			$slotMgr = SlotManager::instance();
			$idMgr = Services::getService("Id");
			try {
				$slot = $slotMgr->getSlotBySiteId($idMgr->getId(SiteDispatcher::getCurrentNodeId()));
				$slot->deleteSiteId();
			} catch (Exception $e) {
				
			}
		}
		
		$director->deleteSiteComponent($component);
	}
	
	/**
	 * Return the browser to the page from whence they came
	 * 
	 * @return void
	 * @access public
	 * @since 10/16/06
	 */
	function returnToCallerPage () {
		$harmoni = Harmoni::instance();
		if ($this->_returnNode) {
			if (!$action = RequestContext::value('returnAction'))
				 $action = "editview";
			RequestContext::locationHeader($harmoni->request->quickURL(
				$harmoni->request->getRequestedModule(), $action,
				array("node" => $this->_returnNode)));	
		} else {
			RequestContext::locationHeader($harmoni->request->quickURL(
				"portal", "list"));
		}
	}
	
	/**
	 * Find a safe return node. If we are deleting the return node or the 
	 * return node is a descendent of the node we are deleting, use the deleted
	 * node's parent as the return node.
	 * 
	 * @param object SiteComponent $componentToDelete
	 * @return void
	 * @access public
	 * @since 10/16/06
	 */
	function findSafeReturnNode ( $director, $componentToDelete ) {
		if (RequestContext::value('returnNode')) {
			// Traverse up to see if the componentToDelete is an ancestor of the 
			// return node or the return node itself.
			$node = $director->getSiteComponentById(RequestContext::value('returnNode'));
			while ($node) {
				if ($componentToDelete->getId() == $node->getId()) {
					$parentComponent = $componentToDelete->getParentComponent();
					$this->_returnNode = $parentComponent->getId();
					return;
				}
				$node = $node->getParentComponent();
			}
			
			// If the return node isn't going to be deleted, just use it.
			$this->_returnNode = RequestContext::value('returnNode');
		} else {
			$this->_returnNode = null;
		}
	}
}

?>