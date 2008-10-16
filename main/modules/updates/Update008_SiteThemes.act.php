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
class Update008_SiteThemesAction
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
		return Date::withYearMonthDay(2008, 5, 20);
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getTitle () {
		return _("Per-Site Themes");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getDescription () {
		return _("This update will add the tables needed for storing custom themes on a per-site basis. These custom themes are copies of distributed themes in which the site administrators can modify the CSS, HTML, and images.");
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
			'segue_site_theme',
			'segue_site_theme_data',
			'segue_site_theme_data_type',
			'segue_site_theme_image',
			'segue_site_theme_thumbnail'
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
				SQLUtils::runSQLfile(MYDIR."/main/SQL/MySQL/04_SiteThemes.sql", IMPORTER_CONNECTION);
				break;
			case ORACLE:
			case POSTGRESQL:
				SQLUtils::runSQLfile(MYDIR."/main/SQL/PostgreSQL/04_SiteThemes.sql", IMPORTER_CONNECTION);
				break;
			default:
				throw new Exception("Database schemas are not defined for specified database type.");
		}
		
		return true;
	}
	
}

?>