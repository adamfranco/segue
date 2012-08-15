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
 * This update adds a table for storing export status.
 * 
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2012, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 */
class Update025_ExportQueueAction
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2012, 8, 14);
		return $date;
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 */
	function getTitle () {
		return _("Export Queue");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 */
	function getDescription () {
		return _("This update adds a table for storing export status.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 */
	function isInPlace () {
		$dbc = Services::getService('DatabaseManager');
		return in_array('site_export_queue', $dbc->getTableList());
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
		$query->addSQLQuery("
CREATE TABLE IF NOT EXISTS `site_export_queue` (
  `slot` varchar(255) collate utf8_bin NOT NULL,
  `priority` int(2) NOT NULL default '0',
  `status` varchar(10) collate utf8_bin default NULL,
  `info` text collate utf8_bin,
  `pid` int(10) default NULL,
  `tstamp` timestamp NULL default NULL on update CURRENT_TIMESTAMP,
  `running_time` float default NULL,
  PRIMARY KEY  (`slot`),
  KEY `to_do` (`status`,`priority`,`slot`),
  KEY `pid` (`pid`)
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