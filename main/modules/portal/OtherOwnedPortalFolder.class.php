<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: OtherOwnedPortalFolder.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/PortalFolder.interface.php");

/**
 * The PersonalPortalFolder contains all personal sites.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: OtherOwnedPortalFolder.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */
class OtherOwnedPortalFolder
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
		return _("Other");
	}
	
	/**
	 * Answer a description of this category for display purposes
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDescription () {
		return _("Other placeholders owned by you that aren't classes.");
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
		return "other_owned";
	}
	
	/**
	 * Answer an array of the slots in this category.
	 * 
	 * @return array of Slot objects
	 * @access public
	 * @since 4/1/08
	 */
	public function getSlots () {
		$slotMgr = SlotManager::instance();
		
		// process of elimination.
		$otherCategorizedSlotNames = $this->getOtherCategorizedSlotNames();
		$slots = array();
		foreach ($slotMgr->getSlots() as $slot) {
			if (!in_array($slot->getShortName(), $otherCategorizedSlotNames))
				$slots[] = $slot;
		}
		return $slots;
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
	 * Answer an array of slot-names in other folders in the Main category.
	 * 
	 * @return array
	 * @access private
	 * @since 4/1/08
	 */
	private function getOtherCategorizedSlotNames () {
		$portalMgr = PortalManager::instance();
		
		$slotNames = array();
		$foldersToIgnoreFrom = array('upcoming_classes', 'current_classes', 'past_classes', 'personal');
		
		foreach ($foldersToIgnoreFrom as $folderId) {
			$folder = $portalMgr->getFolder($folderId);
			if ($folder->getIdString() != $this->getIdString()) {
				foreach ($folder->getSlots() as $slot) {
					$slotNames[] = $slot->getShortname();
				}
			}
		}
		
		return $slotNames;
	}
}

?>