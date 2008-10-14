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
 * This action will return a listing of matching slots for making target aliases.
 * 
 * @since 10/9/08
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class make_aliasAction
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
		header('Content-Type: text/plain;');
		try {
			if (!$this->isAuthorizedToExecute())
				throw new PermissionDeniedException("You must be logged in.");
			
			$mgr = SlotManager::instance();
			$slot = $mgr->getSlotByShortname(RequestContext::value('slot'));
			$slot->makeAlias($mgr->getSlotByShortname(RequestContext::value('target_slot')));
			
			print _("Success");
			
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
				
				$item = new AgentNodeEntryItem("Alias Made", "'".$slot->getShortname()."' has been made an alias of '".RequestContext::value('target_slot')."'.");
				$item->addNodeId($slot->getSiteId());
				
				$log->appendLogWithTypes($item,	$formatType, $priorityType);
			}
		} catch (OperationFailedException $e) {
			print $e->getMessage();
		} catch (UnknownIdException $e) {
			print $e->getMessage();
		}
		exit;
	}
	
}

?>