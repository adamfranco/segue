<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PersonalPortalFolder.class.php,v 1.2 2008/04/02 17:20:36 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/PortalFolder.interface.php");

/**
 * This portal folder contains a list of recently visited sites.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PersonalPortalFolder.class.php,v 1.2 2008/04/02 17:20:36 adamfranco Exp $
 */
class RecentlyVisitedPortalFolder
	implements PortalFolder 
{
		
	/**
	 * Answer a display Name for this category
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDisplayName () {
		return _("Recently Visited");
	}
	
	/**
	 * Answer a description of this category for display purposes
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDescription () {
		return "";
	}
	
	/**
	 * Answer a string Identifier for this folder that is unique within this folder's
	 * category.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getIdString () {
		return "recent_access";
	}
	
	/**
	 * Answer an array of the slots in this category.
	 * 
	 * @return array of Slot objects
	 * @access public
	 * @since 4/1/08
	 */
	public function getSlots () {
		$slots = array();
		$slotMgr = SlotManager::instance();
		
		foreach (Segue_AccessLog::instance()->getRecentSlots() as $shortname) {
			try {
				$slots[] = $slotMgr->getSlotByShortname($shortname);
			} catch (UnknownIdException $e) {
			}
		}
		
		return $slots;
	}
	
	/**
	 * Answer true if the slots returned by getSlots() have already been filtered
	 * by authorization and authorization checks should only be done when printing.
	 * This method enables speed increases on long pre-sorted lists.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/21/08
	 */
	public function slotsAreFilteredByAuthorization () {
		return false;
	}
	
	/**
	 * Answer true if the edit controls should be displayed for the sites listed.
	 * If true, this can lead to slowdowns as authorizations are checked on large
	 * lists of large sites.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/21/08
	 */
	public function showEditControls () {
		return true;
	}
	
	/**
	 * Answer a string of controls html to go along with this folder. In many cases
	 * it will be empty, but some implementations may need controls for adding new slots.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getControlsHtml () {
		return '';
	}
}

?>