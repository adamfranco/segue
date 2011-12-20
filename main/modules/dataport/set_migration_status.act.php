<?php
/**
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
require_once(MYDIR."/main/modules/view/SiteDispatcher.class.php");


/**
 * This action will export a site to an xml file
 * 
 * @since 1/17/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: export.act.php,v 1.8 2008/04/09 21:12:02 adamfranco Exp $
 */
class set_migration_statusAction
	extends Action
{
		
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/14/08
	 */
	public function isAuthorizedToExecute () {
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotByShortname(RequestContext::value('slot'));
		if (!$slot->siteExists()) {
			return $slot->isUserOwner();
		}
		
		// get siteRoot node and check that
		$idMgr = Services::getService('Id');
		$azMgr = Services::getService('AuthZ');
		// Since view AZs cascade up, just check at the node.
		return $azMgr->isUserAuthorized(
			$idMgr->getId('edu.middlebury.authorization.view'),
			$slot->getSiteId());
	}
	
	/**
	 * Execute the action
	 * 
	 * @return mixed
	 * @access public
	 * @since 1/17/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException("You are not authorized to update this slot.");
		
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotByShortname(RequestContext::value('slot'));
		
		// Validate the status and URL, then save.
		$validStatus = array('incomplete', 'archived', 'migrated', 'unneeded');
		if (!in_array(RequestContext::value('status'), $validStatus))
			throw new InvalidArgumentException("Invalid status. Must be one of: ".implode(', ', $validStatus));
		
		if (RequestContext::value('status') == 'migrated') {
			$url = filter_var(RequestContext::value('url'), FILTER_VALIDATE_URL);
			if (!is_string($url) || !strlen($url))
				throw new InvalidArgumentException("Invalid URL.");
		} else {
			$url = '';
		}
		
		$dbc = Services::getService('DBHandler');
		$authN = Services::getService('AuthN');
		
		$query = new InsertQuery;
		$query->setTable('segue_slot_migration_status');
		$query->addValue('shortname', $slot->getShortname());
		$query->addValue('status', RequestContext::value('status'));
		$query->addValue('redirect_url', $url);
		$query->addValue('user_id', $authN->getFirstUserId()->getIdString());
		
		try {
			$result = $dbc->query($query, IMPORTER_CONNECTION);
		} catch (DuplicateKeyDatabaseException $e) {
			$query = new UpdateQuery;
			$query->setTable('segue_slot_migration_status');
			$query->addValue('status', RequestContext::value('status'));
			$query->addValue('redirect_url', $url);
			$query->addValue('user_id', $authN->getFirstUserId()->getIdString());
			$query->addWhereEqual('shortname', $slot->getShortname());
			$result = $dbc->query($query, IMPORTER_CONNECTION);
		}
		
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
			
			$message = "'".$slot->getShortname()."' marked as ".RequestContext::value('status');
			if ($url)
				$message .= " to ".$url;
			$item = new AgentNodeEntryItem("Set Slot Status", $message);
			
			if ($slot->siteExists())
				$item->addNodeId($slot->getSiteId());
			
			$log->appendLogWithTypes($item,	$formatType, $priorityType);
		}
		
		header("Content-Type: text/plain");
		print "Success.";
		
		exit;
	}
}