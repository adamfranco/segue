<?php
/**
 * @since 08/01/08
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
 * his update will remove the invalid primary key from the 'sets' table and add a new index that does not force uniqueness.
 * 
 * @since 08/01/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update002_SlotUniqueKey.act.php,v 1.1 2008/01/04 18:44:00 adamfranco Exp $
 */
class Update013_SetsKeysAction
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 08/01/08
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2008, 8, 1);
		return $date;
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 08/01/08
	 */
	function getTitle () {
		return _("Sets keys");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 08/01/08
	 */
	function getDescription () {
		return _("This update will remove the invalid primary key from the 'sets' table and add a new index that does not force uniqueness.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 08/01/08
	 */
	function isInPlace () {
		$dbc = Services::getService('DatabaseManager');
		$query = new GenericSQLQuery();
		$query->addSQLQuery("SHOW INDEX FROM sets");
		$result =$dbc->query($query);
		$result =$result->returnAsSelectQueryResult();
		
		$hasPrimary = false;
		$hasNewIndex = false;
		while($result->hasMoreRows()) {
			switch(strtolower($result->field('Key_name'))) {
				case "primary":
					$hasPrimary = true;
					break;
				case "set_item_index":
					$hasNewIndex = true;
					break;
			}
			$result->advanceRow();
		}
		$result->free();
		
		return ($hasNewIndex && !$hasPrimary);
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 08/01/08
	 */
	function runUpdate () {
		$dbc = Services::getService('DatabaseManager');
		$slotMgr = SlotManager::instance();
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("ALTER TABLE `sets` DROP PRIMARY KEY;");
		try {
			$dbc->query($query);
		} catch (DatabaseException $e) {
		}
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery(" ALTER TABLE `sets` ADD INDEX `set_item_index` ( `id` , `item_id` ) ;");
		try {
			$dbc->query($query);
		} catch (DatabaseException $e) {
		}
		
		return $this->isInPlace();
	}
}

?>