<?php
/**
 * @since 7/8/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update005_RssAndBreadcrumbs.act.php,v 1.1 2008/03/21 19:18:18 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Install the RSS feed display plugin
 * 
 * @since 7/8/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update005_RssAndBreadcrumbs.act.php,v 1.1 2008/03/21 19:18:18 adamfranco Exp $
 */
class Update012_RssPluginAction
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 7/8/08
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2008, 7, 11);
		return $date;
	}
		
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 7/8/08
	 */
	function getTitle () {
		return _("RSS Cache Size");
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 7/8/08
	 */
	function getDescription () {
		return _("This update will extend the cache data size used for RSS data.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/8/08
	 */
	function isInPlace () {
		if (!$this->checkTable('segue_plugins_rssfeed_cache', 'feed_data'))
			return false;
		
		return true;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/8/08
	 */
	function runUpdate () {
		$this->updateTable('segue_plugins_rssfeed_cache', 'feed_data');
		return $this->isInPlace();
	}
	
	/**
	 * Check table/column
	 * 
	 * @param string $table
	 * @param string $column
	 * @return boolean
	 * @access protected
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
				if (strtolower($result->field(1)) == 'longtext')
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
		$query->addSQLQuery("ALTER TABLE `".$table."` CHANGE `".$column."` `".$column."` LONGTEXT NOT NULL");
		$dbc->query($query);		
		
		return true;
	}
	
}

?>