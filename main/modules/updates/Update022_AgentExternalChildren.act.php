<?php
/**
 * @since 2/25/09
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update001_SlotCategory.act.php,v 1.1 2007/12/06 16:46:55 adamfranco Exp $
 */ 
 
require_once(HARMONI.'/DBHandler/GenericSQLQuery.class.php');
require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add a column to segues's segue_slot table for a location_category
 * 
 * @since 2/25/09
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update001_SlotCategory.act.php,v 1.1 2007/12/06 16:46:55 adamfranco Exp $
 */
class Update022_AgentExternalChildrenAction 
	extends Update
{
		
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 2/25/09
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2009, 2, 25);
		return $date;
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 2/25/09
	 */
	function getTitle () {
		return _("agent_external_children field length");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 2/25/09
	 */
	function getDescription () {
		return _("This update will extend the field length of the agent_external_children columns from 70 characters to 140 characters to prevent long DNs from being truncated.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/25/09
	 */
	function isInPlace () {
		$dbc = Services::getService('DatabaseManager');
		$query = new GenericSQLQuery();
		$query->addSQLQuery("DESCRIBE `agent_external_children`");
		$result =$dbc->query($query);
		$result =$result->returnAsSelectQueryResult();
		
		while($result->hasMoreRows()) {
			if ($result->field(1) != "varchar(140)") {
				return false;
			}
			$result->advanceRow();
		}
		$result->free();
		
		return true;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/25/09
	 */
	function runUpdate () {
		$dbc = Services::getService('DatabaseManager');
		$slotMgr = SlotManager::instance();
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery(" ALTER TABLE `agent_external_children` CHANGE `fk_parent` `fk_parent` VARCHAR( 140 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ,
CHANGE `fk_child` `fk_child` VARCHAR( 140 ) CHARACTER SET utf8 COLLATE utf8_bin NOT NULL ;");
		$dbc->query($query);
				
		return true;
	}
}

?>