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
		
		$slot->setMigrationStatus(RequestContext::value('status'), RequestContext::value('url'));
		
		header("Content-Type: text/plain");
		print "Success.";
		
		exit;
	}
}