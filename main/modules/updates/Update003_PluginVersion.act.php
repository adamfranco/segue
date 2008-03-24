<?php
/**
 * @since 1/11/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update003_PluginVersion.act.php,v 1.2 2008/03/24 16:32:32 adamfranco Exp $
 */ 
 
require_once(HARMONI.'/DBHandler/GenericSQLQuery.class.php');
require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add a column to segues's segue_slot table for a location_category
 * 
 * @since 1/11/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update003_PluginVersion.act.php,v 1.2 2008/03/24 16:32:32 adamfranco Exp $
 */
class Update003_PluginVersionAction
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 1/11/08
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2008, 1, 11);
		return $date;
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 1/11/08
	 */
	function getTitle () {
		return _("Add segue_plugin_version table.");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 1/11/08
	 */
	function getDescription () {
		return _("This update will add a table to store Plugin versions.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/11/08
	 */
	function isInPlace () {
		$dbc = Services::getService('DatabaseManager');
		
		$tables = $dbc->getTableList();
		return in_array('segue_plugin_version', $tables);
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/11/08
	 */
	function runUpdate () {
		$dbc = Services::getService('DatabaseManager');
		$slotMgr = SlotManager::instance();
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("CREATE TABLE segue_plugin_version (
  version_id int(10) unsigned NOT NULL auto_increment,
  node_id varchar(170) collate utf8_bin NOT NULL,
  tstamp timestamp NOT NULL default CURRENT_TIMESTAMP on update CURRENT_TIMESTAMP,
  `comment` varchar(255) collate utf8_bin NOT NULL,
  agent_id varchar(170) collate utf8_bin NOT NULL,
  version_xml longblob NOT NULL,
  PRIMARY KEY  (version_id),
  KEY node_id (node_id)
)
CHARACTER SET utf8
TYPE=InnoDB;");
		$dbc->query($query);		
		
		return true;
	}
}

?>