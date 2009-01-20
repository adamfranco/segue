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

/**
 * Add a table for the segue access log
 * 
 * @since 9/22/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update018_UserDataAction
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
		return Date::withYearMonthDay(2008, 9, 22);
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getTitle () {
		return _("User Data");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getDescription () {
		return _("This update will add the table needed for storing user-data and preferences.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/24/08
	 */
	function isInPlace () {
		$dbc = Services::getService('DatabaseManager');
		
		$siteThemeTables = array(
			'user_prefs'
		);
		
		$tables = $dbc->getTableList();
		// Check for new tables
		foreach ($siteThemeTables as $table) {
			if (!in_array($table, $tables))
				return false;
		}
		
		return true;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/24/08
	 */
	function runUpdate () {
		$dbc = Services::getService("DatabaseManager");
		/*********************************************************
		 * Create the new tables
		 *********************************************************/
		$type = $dbc->getDatabaseType(IMPORTER_CONNECTION);
		switch ($type) {
			case MYSQL:
				SQLUtils::runSQLfile(HARMONI_BASE."/SQL/MySQL/UserPrefs.sql", IMPORTER_CONNECTION);
				break;
			case ORACLE:
			case POSTGRESQL:
				SQLUtils::runSQLfile(HARMONI_BASE."/SQL/PostgreSQL/UserPrefs.sql", IMPORTER_CONNECTION);
				break;
			default:
				throw new Exception("Database schemas are not defined for specified database type.");
		}
		
		return true;
	}
}

?>