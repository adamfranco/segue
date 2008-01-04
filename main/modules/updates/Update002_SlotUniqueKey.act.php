<?php
/**
 * @since 1/4/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update002_SlotUniqueKey.act.php,v 1.1 2008/01/04 18:44:00 adamfranco Exp $
 */ 
 
require_once(HARMONI.'/DBHandler/GenericSQLQuery.class.php');
require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add a column to segues's segue_slot table for a location_category
 * 
 * @since 1/4/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update002_SlotUniqueKey.act.php,v 1.1 2008/01/04 18:44:00 adamfranco Exp $
 */
class Update002_SlotUniqueKeyAction
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
		$date = Date::withYearMonthDay(2008, 1, 4);
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
		return _("Add a unique key to the segue_slot_owner table.");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 7/9/07
	 */
	function getDescription () {
		return _("This update will add a unique key to prevent duplicate owner entries from being stored.");
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
		$query->addSQLQuery("SHOW INDEX FROM segue_slot_owner");
		$result =$dbc->query($query);
		$result =$result->returnAsSelectQueryResult();
		
		$exists = false;
		while($result->hasMoreRows()) {
			if ($result->field('Key_name') == "unique_owner") {
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
		$query->addSQLQuery("ALTER TABLE `segue_slot_owner` ADD UNIQUE `unique_owner` ( `shortname` , `owner_id` ) ;");
		$dbc->query($query);		
		
		return true;
	}
}

?>