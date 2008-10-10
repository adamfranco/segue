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
 * Add a column for slot aliases.
 * 
 * @since 9/22/08
 * @package segue.updates
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Update019_AliasesAction
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
		return Date::withYearMonthDay(2008, 10, 10);
	}
	
	/**
	 * Answer the title of this update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getTitle () {
		return _("Site Aliases");
	}
	
	/**
	 * Answer the description of the update
	 * 
	 * @return string
	 * @access public
	 * @since 3/24/08
	 */
	function getDescription () {
		return _("Add a column for making slot/placeholder aliases.");
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
		
		$query = new GenericSQLQuery();
		$query->addSQLQuery("DESCRIBE segue_slot");
		$result =$dbc->query($query);
		$result =$result->returnAsSelectQueryResult();
		
		$exists = false;
		while($result->hasMoreRows()) {
			if ($result->field(0) == "alias_target") {
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
	 * @since 3/24/08
	 */
	function runUpdate () {
		$dbc = Services::getService('DatabaseManager');
		$slotMgr = SlotManager::instance();
		
		try {
			$query = new GenericSQLQuery();
			$query->addSQLQuery("ALTER TABLE `segue_slot` ADD `alias_target` VARCHAR(50);");
			$dbc->query($query);
		} catch (QueryDatabaseException $e) {
			print "<p>".$e->getMessage()."</p>";
		}
		
		try {
			$query = new GenericSQLQuery();
			$query->addSQLQuery("ALTER TABLE `segue_slot` ADD FOREIGN KEY ( `alias_target` ) REFERENCES `afranco_segue2_prod`.`segue_slot` (`shortname`) ON DELETE SET NULL ON UPDATE CASCADE ;");		
			$dbc->query($query);		
		} catch (QueryDatabaseException $e) {
			print "<p>".$e->getMessage()."</p>";
		}
		
		return $this->isInPlace();
	}
}

?>