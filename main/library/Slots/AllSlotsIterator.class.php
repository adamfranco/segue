<?php
/**
 * @since 12/4/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AllSlotsIterator.class.php,v 1.1 2007/12/06 19:02:03 adamfranco Exp $
 */ 

require_once(HARMONI."oki2/shared/Harmoni_Iterator.interface.php");

/**
 * The AllSlotsIterator provides access to all internally-defined slots.
 * 
 * @since 12/4/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AllSlotsIterator.class.php,v 1.1 2007/12/06 19:02:03 adamfranco Exp $
 */
class AllSlotsIterator
	implements Harmoni_Iterator
{
	
	/**
	 * @var integer $startingNum;  
	 * @access private
	 * @since 12/4/07
	 */
	private $startingNumber = 0;
	
	/**
	 * @var integer $count;  
	 * @access private
	 * @since 12/4/07
	 */
	private $count;
	
	/**
	 * @var array $queue;  
	 * @access private
	 * @since 12/4/07
	 */
	private $queue;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 12/4/07
	 */
	public function __construct () {
		$this->queue = array();
	}
	
	/**
	 * Return true if there is an additional item.
	 * @return boolean
	 * 
	 * @throws object SharedException An exception with one of the
	 *         following messages defined in org.osid.shared.SharedException
	 *         may be thrown:  {@link
	 *         org.osid.shared.SharedException#UNKNOWN_TYPE UNKNOWN_TYPE},
	 *         {@link org.osid.shared.SharedException#PERMISSION_DENIED
	 *         PERMISSION_DENIED}, {@link
	 *         org.osid.shared.SharedException#CONFIGURATION_ERROR
	 *         CONFIGURATION_ERROR}, {@link
	 *         org.osid.shared.SharedException#UNIMPLEMENTED UNIMPLEMENTED}
	 * 
	 * @return boolean
	 * @access public
	 * @since 12/4/07
	 */
	public function hasNext () {
		if (!count($this->queue))
			$this->loadNextBatch();
		
		if (count($this->queue))
			return true;
		
		return false;
	}
	
	/**
	 * Return the next item.
	 *  
	 * @return mixed
	 * 
	 * @throws object SharedException An exception with one of the
	 *         following messages defined in org.osid.shared.SharedException
	 *         may be thrown:  {@link
	 *         org.osid.shared.SharedException#UNKNOWN_TYPE UNKNOWN_TYPE},
	 *         {@link org.osid.shared.SharedException#PERMISSION_DENIED
	 *         PERMISSION_DENIED}, {@link
	 *         org.osid.shared.SharedException#CONFIGURATION_ERROR
	 *         CONFIGURATION_ERROR}, {@link
	 *         org.osid.shared.SharedException#UNIMPLEMENTED UNIMPLEMENTED},
	 *         {@link
	 *         org.osid.shared.SharedException#NO_MORE_ITERATOR_ELEMENTS
	 *         NO_MORE_ITERATOR_ELEMENTS}
	 * 
	 * @access public
	 * @since 12/4/07
	 */
	function next () {
		if (!$this->hasNext())
			throw new NoMoreIteratorElementsException;
		
		return array_shift($this->queue);
	}
	
	/**
	 * Skip past the next item without returning it.
	 *  
	 * @return void
	 * 
	 * @throws object SharedException An exception with one of the
	 *         following messages defined in org.osid.shared.SharedException
	 *         may be thrown:  {@link
	 *         org.osid.shared.SharedException#UNKNOWN_TYPE UNKNOWN_TYPE},
	 *         {@link org.osid.shared.SharedException#PERMISSION_DENIED
	 *         PERMISSION_DENIED}, {@link
	 *         org.osid.shared.SharedException#CONFIGURATION_ERROR
	 *         CONFIGURATION_ERROR}, {@link
	 *         org.osid.shared.SharedException#UNIMPLEMENTED UNIMPLEMENTED},
	 *         {@link
	 *         org.osid.shared.SharedException#NO_MORE_ITERATOR_ELEMENTS
	 *         NO_MORE_ITERATOR_ELEMENTS}
	 * 
	 * @access public
	 * @since 12/4/07
	 */
	function skipNext () {
		$this->next();
	}
	
	/**
	 * Gives an estimate of the number of items in the iterator. This may not be
	 * accurate or the iterator may be infinite. Use hasNext() and next() to find 
	 * the actual number if needed and possible.
	 * 
	 * @throws object SharedException An exception with one of the
	 *         following messages defined in org.osid.shared.SharedException
	 *         may be thrown:  {@link
	 *         org.osid.shared.SharedException#UNKNOWN_TYPE UNKNOWN_TYPE},
	 *         {@link org.osid.shared.SharedException#PERMISSION_DENIED
	 *         PERMISSION_DENIED}, {@link
	 *         org.osid.shared.SharedException#CONFIGURATION_ERROR
	 *         CONFIGURATION_ERROR}, {@link
	 *         org.osid.shared.SharedException#UNIMPLEMENTED UNIMPLEMENTED}
	 * 
	 * @return int
	 * @access public
	 * @since 12/4/07
	 */
	public function count () {
		if (!isset($this->count)) {
			$query = new SelectQuery;
			$query->addColumn('COUNT(*)', 'num');
			$query->addTable('segue_slot');
			$dbc = Services::getService('DBHandler');
			$result = $dbc->query($query, IMPORTER_CONNECTION);
			$this->count = intval($result->field('num'));
		}
		
		return $this->count;
	}
	
	/**
	 * Load the next batch of slots
	 * 
	 * @return void
	 * @access private
	 * @since 12/4/07
	 */
	private function loadNextBatch () {
		$query = new SelectQuery;
		$query->addColumn('shortname');
		$query->addTable('segue_slot');
		$query->startFromRow($this->startingNumber + 1);
		$query->limitNumberOfRows(50);
		$query->addOrderBy('shortname');
		
// 		printpre($query->asString());
		
		$dbc = Services::getService('DBHandler');
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		$slotNames = array();
		while ($result->hasNext()) {
			$slotNames[] = $result->field('shortname');
			$result->next();
			$this->startingNumber++;
		}
		
// 		printpre($slotNames);
		$slotMgr = SlotManager::instance();
		$slots = $slotMgr->loadSlotsFromDb($slotNames);
		foreach ($slots as $slot) {
			$this->queue[] = $slot;		
		}
	}
}

?>