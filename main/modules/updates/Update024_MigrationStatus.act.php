<?php
/**
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */ 
 
require_once(HARMONI.'/DBHandler/GenericSQLQuery.class.php');
require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * This update adds a table for storing migration status.
 * 
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2011, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class Update024_MigrationStatusAction
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2011, 12, 20);
		return $date;
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 */
	function getTitle () {
		return _("Migration Status");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 */
	function getDescription () {
		return _("This update adds a table for storing migration status.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 */
	function isInPlace () {
		$dbc = Services::getService('DatabaseManager');
		return in_array('segue_slot_migration_status', $dbc->getTableList());
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 */
	function runUpdate () {
		$dbc = Services::getService('DatabaseManager');		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("CREATE TABLE IF NOT EXISTS segue_slot_migration_status (
  shortname varchar(50) collate utf8_bin NOT NULL,
  `status` enum('incomplete','archived','migrated','unneeded') collate utf8_bin NOT NULL default 'incomplete',
  status_date timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  redirect_url text collate utf8_bin NOT NULL,
  user_id int(11) NOT NULL,
  PRIMARY KEY  (shortname)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_bin;");
		try {
			$dbc->query($query);
		} catch (DatabaseException $e) {
			print "<p>".$e->getMessage()."</p>";
		}
		
		return $this->isInPlace();
	}
}

?>