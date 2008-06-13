<?php
/**
 * @since 7/9/07
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update001_SlotCategory.act.php,v 1.1 2007/12/06 16:46:55 adamfranco Exp $
 */ 
 
require_once(HARMONI.'/DBHandler/GenericSQLQuery.class.php');
require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add a column to segues's segue_slot table for a location_category
 * 
 * @since 7/9/07
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update001_SlotCategory.act.php,v 1.1 2007/12/06 16:46:55 adamfranco Exp $
 */
class Update001_SlotCategoryAction 
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 7/9/07
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2007, 12, 6);
		return $date;
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 7/9/07
	 */
	function getTitle () {
		return _("segue_slot.location_category column");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 7/9/07
	 */
	function getDescription () {
		return _("This update will add a column to store the location category of each slot for determining where the slot should be displayed.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/9/07
	 */
	function isInPlace () {
		$dbc = Services::getService('DatabaseManager');
		$query = new GenericSQLQuery();
		$query->addSQLQuery("DESCRIBE `segue_slot`");
		$result =$dbc->query($query);
		$result =$result->returnAsSelectQueryResult();
		
		$exists = false;
		while($result->hasMoreRows()) {
			if ($result->field(0) == "location_category") {
				$exists = true;
				break;
			}
			$result->advanceRow();
		}
		$result->free();
		
		return $exists;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/9/07
	 */
	function runUpdate () {
		$dbc = Services::getService('DatabaseManager');
		$slotMgr = SlotManager::instance();
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("ALTER TABLE `segue_slot` ADD `location_category` ENUM( 'main', 'community' ) NOT NULL ;");
		$dbc->query($query);
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("ALTER TABLE `segue_slot` ADD INDEX ( `location_category` ) ;");		
		$dbc->query($query);
		
		// Update the slots
		$slots = $slotMgr->getAllSlots();
		while ($slots->hasNext()) {
			$slot = $slots->next();
			printpre("Setting category ".$slot->getDefaultLocationCategory()." for ".$slot->getShortname());
			$slot->setLocationCategory($slot->getDefaultLocationCategory());
		}
		
		
		return true;
	}
}

?>