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
		$slot->makeNotAlias();
		
		$harmoni = Harmoni::instance();
		RequestContext::sendTo($harmoni->request->quickURL('portal', 'list'));
	}
	
}

?>