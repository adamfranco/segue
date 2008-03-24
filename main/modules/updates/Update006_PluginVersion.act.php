<?php
/**
 * @since 3/24/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update006_PluginVersion.act.php,v 1.1 2008/03/24 16:32:32 adamfranco Exp $
 */ 

require_once(HARMONI.'/DBHandler/GenericSQLQuery.class.php');
require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Update the version_xml field to increase its size
 * 
 * @since 3/24/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update006_PluginVersion.act.php,v 1.1 2008/03/24 16:32:32 adamfranco Exp $
 */
class Update006_PluginVersionAction
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
		$date = Date::withYearMonthDay(2008, 3, 24);
		return $date;
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getTitle () {
		return _("Extend plugin version size");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getDescription () {
		return _("This update will convert the Plugin system's segue_plugin_version.version_xml field and the DataManager's dm_blob.data field to be 'longblob's in order to allow larger content.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/24/08
	 */
	function isInPlace () {
		if (!$this->checkTable('segue_plugin_version', 'version_xml'))
			return false;
		if (!$this->checkTable('dm_blob', 'data'))
			return false;
		
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
		$this->updateTable('segue_plugin_version', 'version_xml');
		$this->updateTable('dm_blob', 'data');
		return $this->isInPlace();
	}
	
	/**
	 * Check table/column
	 * 
	 * @param string $table
	 * @param string $column
	 * @return boolean
	 * @access private
	 * @since 3/24/08
	 */
	private function checkTable ($table, $column) {
		$dbc = Services::getService('DatabaseManager');
		
		$tables = $dbc->getTableList();
		if (!in_array($table, $tables))
			return false;
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("DESCRIBE `".$table."`");
		$result =$dbc->query($query);
		$result =$result->returnAsSelectQueryResult();
		
		while($result->hasMoreRows()) {
			if ($result->field(0) == $column) {
				if (strtolower($result->field(1)) == 'longblob')
					return true;
				else
					return false;
			}
			$result->advanceRow();
		}
		$result->free();
		
		return false;
	}
	
	/**
	 * Update a table/column
	 * 
	 * @param string $table
	 * @param string $column
	 * @return boolean
	 * @access private
	 * @since 3/24/08
	 */
	private function updateTable ($table, $column) {
		$dbc = Services::getService('DatabaseManager');
		
		$tables = $dbc->getTableList();
		if (!in_array($table, $tables)) {
			return false;
		}
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("ALTER TABLE `".$table."` CHANGE `".$column."` `".$column."` LONGBLOB NOT NULL");
		$dbc->query($query);		
		
		return true;
	}
	
}

?>