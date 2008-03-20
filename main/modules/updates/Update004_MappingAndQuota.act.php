<?php
/**
 * @since 3/20/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update004_MappingAndQuota.act.php,v 1.1 2008/03/20 20:42:57 adamfranco Exp $
 */ 

require_once(HARMONI.'/DBHandler/GenericSQLQuery.class.php');
require_once(dirname(__FILE__)."/Update.abstract.php");

/**
 * Add the new segue1map table and a column to the segue_slot table for media quotas
 * 
 * @since 3/20/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Update004_MappingAndQuota.act.php,v 1.1 2008/03/20 20:42:57 adamfranco Exp $
 */
class Update004_MappingAndQuotaAction
	extends Update
{
	/**
	 * Answer the date at which this updator was introduced
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 3/20/08
	 */
	function getDateIntroduced () {
		$date = Date::withYearMonthDay(2008, 3, 20);
		return $date;
	}
		
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/20/08
	 */
	function getTitle () {
		return _("Mapping and Quota Table Updates");
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/20/08
	 */
	function getDescription () {
		return _("This update will add the segue1_id_map table and add quota column to segue_slot.");
	}
	
	/**
	 * Answer true if this update is in place
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/20/08
	 */
	function isInPlace () {
		$dbc = Services::getService('DatabaseManager');
		
		$tables = $dbc->getTableList();
		if (!in_array('segue1_id_map', $tables))
			return false;
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("DESCRIBE segue_slot");
		$result =$dbc->query($query);
		$result =$result->returnAsSelectQueryResult();
		
		$exists = false;
		while($result->hasMoreRows()) {
			if ($result->field(0) == "media_quota") {
				$exists = true;
				break;
			}
			$result->advanceRow();
		}
		$result->free();
		
		return $exists;
	}
	
	/**
	 * Run the update
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/20/08
	 */
	function runUpdate () {
		$dbc = Services::getService('DatabaseManager');
		$slotMgr = SlotManager::instance();
		
		try {
			$query = new GenericSQLQuery();
			$query->addSQLQuery("ALTER TABLE `segue_slot` ADD `media_quota` INT;");
			$dbc->query($query);
		} catch (QueryDatabaseException $e) {
			print "<p>".$e->getMessage()."</p>";
		}
		
		try {
			$query = new GenericSQLQuery();
			$query->addSQLQuery("CREATE TABLE segue1_id_map (
  segue1_slot_name varchar(50) NOT NULL,
  segue1_id varchar(50) NOT NULL,
  segue2_slot_name varchar(50) NOT NULL,
  segue2_id varchar(170) NOT NULL,
  PRIMARY KEY  (segue1_id),
  UNIQUE KEY old_id_unique (segue1_slot_name,segue1_id),
  UNIQUE KEY new_id_unique (segue2_slot_name,segue2_id),
  KEY segue2_id (segue2_id)
) ENGINE=InnoDB COMMENT='Mapping between segue1 and segue2 ids for auto-redirects.';");		
			$dbc->query($query);		
		} catch (QueryDatabaseException $e) {
			print "<p>".$e->getMessage()."</p>";
		}
		
		return $this->isInPlace();
	}
}

?>