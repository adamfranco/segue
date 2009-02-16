<?php
/**
 * @since 2/16/09
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(MYDIR.'/main/modules/roles/search_agents.act.php');

/**
 * This action will search for users and return an XML document with the matches.
 * 
 * @since 2/16/09
 * @package segue.ui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class add_site_search_agentsAction
	extends search_agentsAction
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
		if (RequestContext::value("slot")) {
			$slotMgr = SlotManager::instance();
			$slot = $slotMgr->getSlotByShortname(RequestContext::value("slot"));
			if ($slot->isUserOwner())
				return true;
			else
				return false;
		} else {
			$authZ = Services::getService("AuthZ");
			$idManager = Services::getService("Id");
			 
			return $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.add_children"),
				$idManager->getId("edu.middlebury.segue.sites_repository"));
		}
	}
	

	
}

?>