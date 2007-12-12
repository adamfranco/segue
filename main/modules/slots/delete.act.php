<?php
/**
 * @since 12/12/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: delete.act.php,v 1.1 2007/12/12 17:16:31 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(MYDIR."/main/modules/roles/AgentSearchSource.class.php");

/**
 * Delete a slot.
 * 
 * @since 12/12/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: delete.act.php,v 1.1 2007/12/12 17:16:31 adamfranco Exp $
 */
class deleteAction
	extends MainWindowAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 12/04/07
	 */
	function isAuthorizedToExecute () {
		// Check for authorization
 		$authZManager = Services::getService("AuthZ");
 		$idManager = Services::getService("IdManager");
 		return $authZManager->isUserAuthorized(
 					$idManager->getId("edu.middlebury.authorization.add_children"),
 					$idManager->getId("edu.middlebury.authorization.root"));
	}
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 12/7/07
	 */
	public function buildContent () {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("slots");
		$name = strtolower(RequestContext::value("name"));
		$harmoni->request->passthrough("name");
		$harmoni->request->endNamespace();
		
		$slotMgr = SlotManager::instance();
		$slot = $slotMgr->getSlotByShortname($name);
		
		if ($slot->siteExists())
			throw new PermissionDeniedException("You cannot delete a slot that has an existing site.");
		
		$slotMgr->deleteSlot($name);
		
		$harmoni->request->sendTo($this->getReturnUrl());
	}
	
	/**
	 * Answer the return URL
	 * 
	 * @return string
	 * @access public
	 * @since 12/7/07
	 */
	public function getReturnUrl () {
		$harmoni = Harmoni::instance();
		$harmoni->request->forget("name");
		return $harmoni->request->quickURL('slots', 'browse');
	}
	
}

?>