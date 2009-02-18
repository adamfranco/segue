<?php
/**
 * @since 9/22/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/Update.abstract.php");
require_once(dirname(__FILE__)."/../portal/AllVisiblePortalFolder.class.php");

/**
 * Add a column for slot aliases.
 * 
 * @since 9/22/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update020_MembershipAction
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/24/08
	 */
	function getDateIntroduced () {
		return Date::withYearMonthDay(2008, 10, 17);
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getTitle () {
		return _("Site Members");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getDescription () {
		return _("This update will create a group to contain all site-member groups and add a site-members group for each site.'");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/24/08
	 */
	function isInPlace () {
		$agentMgr = Services::getService('Agent');
		$idMgr = Services::getService('Id');
		
		try {
			$group = $agentMgr->getGroup($idMgr->getId('edu.middlebury.segue.site-members'));
			return true;
		} catch (UnknownIdException $e) {
			return false;
		}
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/24/08
	 */
	function runUpdate () {
		$agentMgr = Services::getService('Agent');
		$idMgr = Services::getService('Id');
		
		$groupType = new Type ("System", "edu.middlebury.harmoni", "ApplicationGroups", "");
		$nullType = new Type ("System", "edu.middlebury.harmoni", "NULL");
		$nullProperties = new HarmoniProperties($nullType);
			
		try {
			$containerGroup = $agentMgr->getGroup($idMgr->getId('edu.middlebury.segue.site-members'));
		} catch (UnknownIdException $e) {
			$containerGroup = $agentMgr->createGroup('Site-Members', 
				$groupType, 
				'A container for site-membership groups',
				$nullProperties,
				$idMgr->getId('edu.middlebury.segue.site-members'));
		}
		
		// Since updates can only be run by admins, which in turn can see all sites,
		// the all visible folder will get all slots.
		$folder = new AllVisiblePortalFolder();
		$slots = $folder->getSlots();
		$status = new StatusStars(_("Checking Nodes"));
		$status->initializeStatistics(count($slots));
		$director = SiteDispatcher::getSiteDirector();
		foreach ($slots as $slot) {
			$site = $director->getSiteComponentById($slot->getSiteId()->getIdString());
			$site->getMembersGroup();
			$status->updateStatistics();
		}
		
		return $this->isInPlace();
	}
}

?>