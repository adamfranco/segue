<?php
/**
 * @since 10/9/08
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * This action will remove an alias from the slot
 * 
 * @since 10/9/08
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class remove_aliasAction
	extends Action
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 10/9/08
	 */
	public function isAuthorizedToExecute () {
		$slot = SlotManager::instance()->getSlotByShortname(RequestContext::value('slot'));
		return $slot->isUserOwner();
	}
	
	/**
	 * Execute the action
	 * 
	 * @return void
	 * @access public
	 * @since 10/9/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException("You must be logged in.");
			
		$mgr = SlotManager::instance();
		$slot = $mgr->getSlotByShortname(RequestContext::value('slot'));
		$oldTarget = $slot->getAliasTarget();
		$slot->makeNotAlias();
		
		/*********************************************************
		 * Log the success
		 *********************************************************/
		if (Services::serviceRunning("Logging")) {
			$loggingManager = Services::getService("Logging");
			$log = $loggingManager->getLogForWriting("Segue");
			$formatType = new Type("logging", "edu.middlebury", "AgentsAndNodes",
							"A format in which the acting Agent[s] and the target nodes affected are specified.");
			$priorityType = new Type("logging", "edu.middlebury", "Event_Notice",
							"Normal events.");
			
			$item = new AgentNodeEntryItem("Alias Removed", "'".$slot->getShortname()."' is no longer an alias of '".$oldTarget->getShortname()."'.");
			$item->addNodeId($oldTarget->getSiteId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		$harmoni = Harmoni::instance();
		RequestContext::sendTo($harmoni->request->quickURL('portal', 'list'));
	}
	
}

?>