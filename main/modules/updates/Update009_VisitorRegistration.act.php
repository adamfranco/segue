<?php
/**
 * @since 5/20/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add tables for the SiteThemes
 * 
 * @since 5/20/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update009_VisitorRegistrationAction
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
		return Date::withYearMonthDay(2008, 6, 5);
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getTitle () {
		return _("Visitor Registration");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getDescription () {
		return _("This update will add the table needed for visitor registration.");
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
		
		$neededTables = array(
			'auth_visitor'
		);
		
		$tables = $dbc->getTableList();
		// Check for new tables
		foreach ($neededTables as $table) {
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
				SQLUtils::runSQLfile(HARMONI_BASE."/SQL/MySQL/AuthN_Visitor_Authentication.sql", IMPORTER_CONNECTION);
				break;
			case POSTGRESQL:
			case ORACLE:
				SQLUtils::runSQLfile(HARMONI_BASE."/SQL/PostgreSQL/004_AuthN_Visitor_Authentication.sql", IMPORTER_CONNECTION);
				break;
			default:
				throw new Exception("Database schemas are not defined for specified database type.");
		}
		
		return true;
	}
	
}

?>