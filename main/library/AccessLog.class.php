<?php
/**
 * @since 9/22/08
 * @package segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * The Segue Access Log maintains a listing of the sites a user has visited. 
 * Sites are listed only at the most recent access time. This log is used to enable
 * shortcuts to recently accessed sites.
 * 
 * @since 9/22/08
 * @package segue
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class Segue_AccessLog {
		
	/**
 	 * @var object  $instance;  
 	 * @access private
 	 * @since 10/10/07
 	 * @static
 	 */
 	private static $instance;

	/**
	 * This class implements the Singleton pattern. There is only ever
	 * one instance of the this class and it is accessed only via the 
	 * ClassName::instance() method.
	 * 
	 * @return object 
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	public static function instance () {
		if (!isset(self::$instance))
			self::$instance = new Segue_AccessLog;
		
		return self::$instance;
	}
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access private
	 * @since 9/16/08
	 */
	private function __construct () {
		if (!isset($_SESSION['segue_access_log']))
			$_SESSION['segue_access_log'] = array();
		
		$this->_syncSessionLog();
	}
	
	/**
	 * Mark a slot as being accessed. 
	 * This method should be called every time a page for a site is visited.
	 * 
	 * @param string $slotname
	 * @return void
	 * @access public
	 * @since 9/22/08
	 */
	public function touch ($slotname) {
		ArgumentValidator::validate($slotname, NonzeroLengthStringValidatorRule::getRule());
		
		if ($this->_storePersistently()) {
			
			$this->_recordVisit($slotname);
		} 
		// Maintain a session-record for anonymous/admin users
		else {
			$_SESSION['segue_access_log'][$slotname] = DateAndTime::now()->asString();
		}
	}
	
	/**
	 * Answer a list of most recently seen slot-names ordered recent-first.
	 * 
	 * @return array
	 * @access public
	 * @since 9/22/08
	 */
	public function getRecentSlots () {
		$slots = array();
		
		$dbc = Services::getService('DatabaseManager');
		
		$query = new SelectQuery;
		$query->addTable('segue_accesslog');
		$query->addColumn('fk_slotname');
		$query->addColumn('tstamp');
		$query->addWhereEqual('agent_id', $this->_getCurrentAgentId());
		$query->addOrderBy('tstamp', DESCENDING);
		$query->limitNumberOfRows(50);
		
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		while ($result->hasNext()) {
			$row = $result->next();
			$slots[$row['fk_slotname']] = DateAndTime::fromString($row['tstamp'])->asString();
		}
		
		// Add session-stored slots
		if (isset($_SESSION['segue_access_log'])) {
			foreach ($_SESSION['segue_access_log'] as $slotname => $tstamp)
				$slots[$slotname] = $tstamp;
			
			arsort($slots);
		}
		
		return array_keys($slots);
	}
	
	/**
	 * Synchronize any sites viewed while not logged in with the database if
	 * we now know the current user.
	 * 
	 * @return void
	 * @access protected
	 * @since 9/22/08
	 */
	protected function _syncSessionLog () {
		if ($this->_storePersistently() && count($_SESSION['segue_access_log'])) {
			// Store any sites visited while not logged in.
			foreach ($_SESSION['segue_access_log'] as $s_slot => $s_timestamp) {
				$this->_recordPastVisit($s_slot, $s_timestamp);
			}
			$_SESSION['segue_access_log'] = array();
		}
	}
	
	/**
	 * Record a visit in the database
	 * 
	 * @param string $slotname
	 * @return void
	 * @access protected
	 * @since 9/22/08
	 */
	protected function _recordVisit ($slotname) {
		$dbc = Services::getService('DatabaseManager');
			
		// First try running an update query, since most will be updates
		$query = new UpdateQuery;
		$query->setTable('segue_accesslog');
		$query->addRawValue('tstamp', 'NOW()');
		$query->addWhereEqual('agent_id', $this->_getCurrentAgentId());
		$query->addWhereEqual('fk_slotname', $slotname);
		
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		// If no rows were updated, insert a new one for this user/slot
		if (!$result->getNumberOfRows()) {
			$query = new InsertQuery;
			$query->setTable('segue_accesslog');
			$query->addRawValue('tstamp', 'NOW()');
			$query->addValue('agent_id', $this->_getCurrentAgentId());
			$query->addValue('fk_slotname', $slotname);
			
			try {
				$dbc->query($query, IMPORTER_CONNECTION);
			} catch (DuplicateKeyDatabaseException $e) {
				// multiple requests may colide, just ignore.
			}
		}
	}
	
	/**
	 * Record a visit in the database
	 * 
	 * @param string $slotname
	 * @param string $timestamp
	 * @return void
	 * @access protected
	 * @since 9/22/08
	 */
	protected function _recordPastVisit ($slotname, $timestamp) {
		$dbc = Services::getService('DatabaseManager');
			
		// First try running an update query, since most will be updates
		$query = new UpdateQuery;
		$query->setTable('segue_accesslog');
		$query->addValue('tstamp', $timestamp);
		$query->addWhereEqual('agent_id', $this->_getCurrentAgentId());
		$query->addWhereEqual('fk_slotname', $slotname);
		$query->addWhereLessThan('tstamp', $timestamp);
		
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		// If no rows were updated, insert a new one for this user/slot
		if (!$result->getNumberOfRows()) {
			try {
				$query = new InsertQuery;
				$query->setTable('segue_accesslog');
				$query->addValue('tstamp', $timestamp);
				$query->addValue('agent_id', $this->_getCurrentAgentId());
				$query->addValue('fk_slotname', $slotname);
				
				$dbc->query($query, IMPORTER_CONNECTION);
				
			// If the update query failed the more recent time where clause, 
			// this insert query will fail. That is fine, just ignore.
			} catch (DuplicateKeyDatabaseException $e) {
			}
		}
	}
	
	/**
	 * Answer the current agentId
	 * 
	 * @return string
	 * @access protected
	 * @since 9/16/08
	 */
	protected function _getCurrentAgentId () {
		if (!isset($this->_currentAgentId)) {
			$authN = Services::getService('AuthN');
			$this->_currentAgentId = $authN->getFirstUserId()->getIdString();
		}
		
		return $this->_currentAgentId;
	}
	
	/**
	 * Answer true if preferences should be stored persistantly.
	 * 
	 * @return boolean
	 * @access protected
	 * @since 9/16/08
	 */
	protected function _storePersistently () {
		// Anonymous will never be persisted.
		if ($this->_getCurrentAgentId() == 'edu.middlebury.agents.anonymous')
			return false;
		
		// Check to see if we are an admin acting as another user
		if (isset($_SESSION['__ADMIN_IDS_ACTING_AS_OTHER']) && count($_SESSION['__ADMIN_IDS_ACTING_AS_OTHER']))
			return false;
		
		// For normal logged in users.
		return true;
	}
	
}

?>