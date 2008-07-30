<?php
/**
 * @since 7/28/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/XmlAction.class.php');

/**
 * Set a session var for the selected slot.
 * 
 * @since 7/28/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class select_for_copyAction
	extends XmlAction
{
	
	/**
	 * Check that the user is authorized to select the slot
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/28/08
	 */
	public function isAuthorizedToExecute () {
		$slot = $this->getSlot();
		$siteAsset = $slot->getSiteAsset();
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		if ($authZ->isUserAuthorized(
			// Currently just check for modify to see if there is 'site-level editor' access.
			// In the future, maybe this should be its own authorization.
			$idMgr->getId('edu.middlebury.authorization.modify'), 
			$siteAsset->getId()))
		{
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return void
	 * @access public
	 * @since 7/28/08
	 */
	public function execute () {
		$this->start();
		try {
		
			if (!$this->isAuthorizedToExecute())
				$this->error(_("Your are not authorized to select this site."), "PermissionDenied");
			
			$slot = $this->getSlot();
			$_SESSION['portal_slot_selection'] = $slot->getShortname();
			print "success";
		
		} catch (Exception $e) {
			$this->error($e->getMessage(), get_class($e));
		}
		$this->end();
	}
	
	/**
	 * Answer the slot.
	 * 
	 * @return object Slot
	 * @access protected
	 * @since 7/28/08
	 */
	protected function getSlot () {
		if (!preg_match('/^[a-z0-9_-]+$/i', RequestContext::value('slot')))
			throw new InvalidArgumentException("Invalid slot name.");
		
		$slotMgr = SlotManager::instance();
		return $slotMgr->getSlotByShortname(RequestContext::value('slot'));
	}
}

?>