<?php
/**
 * @since 8/14/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SlotManager.class.php,v 1.2 2007/08/22 20:08:51 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/CustomSlot.class.php");
require_once(dirname(__FILE__)."/PersonalSlot.class.php");
require_once(dirname(__FILE__)."/CourseSlot.class.php");


/**
 * The Slot manager handles creating and accessing Slots. Slots are the placeholders 
 * for sites that can be created and maintain information such as a shortname
 * for the site, the owner (responsible party) for the site, and the type of the 
 * site (class, personal, other).
 * 
 * @since 8/14/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SlotManager.class.php,v 1.2 2007/08/22 20:08:51 adamfranco Exp $
 */
class SlotManager {
		
	/**
	 * @var object SlotManager $instance;  
	 * @access private
	 * @static
	 * @since 8/14/07
	 */
	private static $instance;
	
	/**
	 * Answer the instance of this object
	 * 
	 * @return object SlotManager
	 * @access public
	 * @since 8/14/07
	 */
	public static function instance () {
		if(!self::$instance) {
			self::$instance = new SlotManager();
		}
		
		return self::$instance;
	}
	
	/**
	 * @var array $slotTypes; 
	 * @access private
	 * @since 8/14/07
	 */
	private $slotTypes;
	
	/**
	 * @var array $slots; A cache of slot objects
	 * @access private
	 * @since 8/14/07
	 */
	private $slots;
	
	/**
	 * Constructor, private to make sure that no one build this object like this:
	 * <code>$slotManager = new SlotManager()</code>
	 *
	 * @return void
	 * @access private
	 * @since 8/14/07
	 */
	private function __construct() {
		$this->slotTypes = array(
			Slot::custom => "CustomSlot",
			Slot::course => "CourseSlot",
			Slot::personal => "PersonalSlot"
		);
		
		$this->slots = array();
	}
	 
	/**
	 * Nothing to do here, make sure that no one will get a copy of this object.
	 * @access private
	 * @return void
	 */
	private function __clone() {}
	
	/**
	 * Answer an array of slots of the classification specified, that are owned 
	 * by the current user.
	 * 
	 * @param string $type Slot::custom, Slot::course, or Slot::personal
	 * @return array
	 * @access public
	 * @since 8/14/07
	 */
	public function getSlotsByType ( $slotType ) {
		if (!isset($this->slotTypes[$slotType])) {
			throw new Exception ("Unknown SlotType, $slotType.");
		}
		
		$slotClass = $this->slotTypes[$slotType];
		
		eval('$extSlots = '.$slotClass.'::getExternalSlotDefinitionsForUser();');
		$intSlots = $this->getInternalSlotDefinitionsForUserByType($slotType);
		
		$slots = $this->mergeSlots($extSlots, $intSlots);
		foreach ($slots as $slot)
			$this->slots[$slot->getShortname()] = $slot;
		
		return $slots;
	}
	
	/**
	 * Answer all slots for the current user
	 * 
	 * @return array
	 * @access public
	 * @since 8/16/07
	 */
	public function getAllSlots () {
		$slots = array();
		foreach ($this->slotTypes as $type => $classname) {
			$slots = array_merge($slots, $this->getSlotsByType($type));
		}
		
		return $slots;
	}
	
	/**
	 * Answer a slot based on shortname
	 * 
	 * @param string shortname
	 * @return array
	 * @access public
	 * @since 8/16/07
	 */
	public function getSlotByShortname ($shortname) {
		if (!isset($this->slots[$shortname])) {
			$this->getAllSlots();
			
			if (!isset($this->slots[$shortname])) {
				$this->loadSlotsFromDB(array($shortname));
			}
			
			if (!isset($this->slots[$shortname])) {
				$slotClass = $this->slotTypes[Slot::custom];
				$this->slots[$shortname] = new $slotClass($shortname);
			}
		}
		
		return $this->slots[$shortname];
	}
	
	/**
	 * Answer the slot that matches the given site id
	 * 
	 * @param string $siteId
	 * @return object Slot
	 * @access public
	 * @since 8/16/07
	 */
	public function getSlotBySiteId ($siteId) {
		// Check our cache
		foreach ($this->slots as $slot) {
			if ($slot->getSiteId() == $siteId)
				return $slot;
		}
		
		// Look up the slot in the database;
		$query = new SelectQuery;
		$query->addTable('segue_slot');
		$query->addTable('segue_slot_owner AS all_owners', LEFT_JOIN, 'segue_slot.shortname = all_owners.shortname');
		
		$query->addColumn('segue_slot.shortname', 'shortname');
		$query->addColumn('segue_slot.site_id', 'site_id');
		$query->addColumn('segue_slot.type', 'type');
		$query->addColumn('all_owners.owner_id', 'owner_id');
		$query->addColumn('all_owners.removed', 'removed');
		
		$query->addWhereEqual('segue_slot.site_id', $siteId);
		
				
// 		print $query->asString();
		$dbc = Services::getService('DBHandler');
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		if ($result->getNumberOfRows()) {
			$slots = $this->getSlotsFromQueryResult($result);
			if (count($slots) !== 1)
				throw new Exception ("Mismached number of slots.");
			
			$slot = $slots[0];
			$slot->mergeWithExternal();
			$this->slots[$slot->getShortname()] = $slot;
		} else {
			throw new Exception("No Slot Found for site id, '$siteId'");
		}
		
		return $slot;
	}
	
	/**
	 * Load a number of slots from the database
	 * 
	 * @param array $slotShortnames
	 * @return object Slot
	 * @access public
	 * @since 8/16/07
	 */
	public function loadSlotsFromDb ($slotShortnames) {
		// Check our cache
		$toLoad = array();
		foreach ($slotShortnames as $shortname) {
			if (!isset($this->slots[$shortname]))
				$toLoad[] = $shortname;
		}
		
		if (count($toLoad)) {
		
			// Look up the slot in the database;
			$query = new SelectQuery;
			$query->addTable('segue_slot');
			$query->addTable('segue_slot_owner AS all_owners', LEFT_JOIN, 'segue_slot.shortname = all_owners.shortname');
			
			$query->addColumn('segue_slot.shortname', 'shortname');
			$query->addColumn('segue_slot.site_id', 'site_id');
			$query->addColumn('segue_slot.type', 'type');
			$query->addColumn('all_owners.owner_id', 'owner_id');
			$query->addColumn('all_owners.removed', 'removed');
			
			$query->addWhereIn('segue_slot.shortname', $toLoad);
			
					
	// 		print $query->asString();
			$dbc = Services::getService('DBHandler');
			$result = $dbc->query($query, IMPORTER_CONNECTION);
			
			$slots = $this->getSlotsFromQueryResult($result);
				
			foreach ($slots as $slot) {
				$slot->mergeWithExternal();
				$this->slots[$slot->getShortname()] = $slot;
			}
		}
	}
	
	/**
	 * Answer the internal slots for the current user
	 * 
	 * @return array
	 * @access private
	 * @since 8/14/07
	 */
	private function getInternalSlotDefinitionsForUserByType ($slotType) {
		$query = new SelectQuery;
		$query->addTable('segue_slot_owner AS search_owner');
		$query->addTable('segue_slot', LEFT_JOIN, 'segue_slot.shortname = search_owner.shortname');
		$query->addTable('segue_slot_owner AS all_owners', LEFT_JOIN, 'segue_slot.shortname = all_owners.shortname');
		
		$query->addColumn('segue_slot.shortname', 'shortname');
		$query->addColumn('segue_slot.site_id', 'site_id');
		$query->addColumn('segue_slot.type', 'type');
		$query->addColumn('all_owners.owner_id', 'owner_id');
		$query->addColumn('all_owners.removed', 'removed');
		
		$query->addWhereEqual('segue_slot.type', $slotType);
		
		$authN = Services::getService("AuthN");
		$userId = $authN->getFirstUserId();
// 		$idManager = Services::getService("Id");
// 		$userId = $idManager->getId("3"); // jadministrator
		
		$query->addWhereEqual('search_owner.owner_id', $userId->getIdString());
		$query->addWhereEqual('search_owner.removed', '0');
		
// 		print $query->asString();
		$dbc = Services::getService('DBHandler');
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		return $this->getSlotsFromQueryResult($result);
	}
	
	
	
	/**
	 * Answer slot objects defined in a query result
	 * 
	 * @param object QueryResult
	 * @return array
	 * @access private
	 * @since 8/14/07
	 */
	private function getSlotsFromQueryResult ($result) {
		$slots = array();
		while ($result->hasMoreRows()) {
			if ($result->field('shortname') !== '') {
				$slotType = $result->field('type');
				if (!isset($this->slotTypes[$slotType]))
					throw new Exception ("Unknown SlotType, '$slotType'.");
				
				$slotClass = $this->slotTypes[$slotType];
				$slot = new $slotClass ($result->field('shortname'), true);
				
				// Add site ids from DB if it exists
				if ($result->field('site_id') !== '')
					$slot->populateSiteId($result->field('site_id'));
				
				
				while($result->hasMoreRows() && $slot->getShortname() == $result->field('shortname')) {
					if ($result->field('owner_id') !== '') {
						if (intval($result->field('removed')))
							$slot->populateRemovedOwnerId($result->field('owner_id'));
						else
							$slot->populateOwnerId($result->field('owner_id'));		
					}
					$result->advanceRow();
				}
				
				$slots[$slot->getShortname()] = $slot;
			}
		}
		$result->free();
		
		return $slots;
	}
	
	/**
	 * Merge Slot definitions so that any externally defined
	 * Slots are matched with internally defined slots
	 * 
	 * @param array $extSlots Externally defined Slots.
	 * @param array $intSlots internally defined Slots.
	 * @return array
	 * @access private
	 * @since 8/14/07
	 */
	private function mergeSlots (array $extSlots, array $intSlots) {
		$slots = array();
		foreach ($extSlots as $extSlot) {
			if (isset($intSlots[$extSlot->getShortname()])) {
				$extSlot->mergeWithInternal($intSlots[$extSlot->getShortname()]);
				unset($intSlots[$extSlot->getShortname()]);
			}
			$slots[$extSlot->getShortname()] = $extSlot;
		}
		
		foreach ($intSlots as $intSlot) {
			if (!isset($slots[$intSlot->getShortname()]))
				$slots[$intSlot->getShortname()] = $intSlot;
		}
		
		ksort($slots);
		
		return $slots;
	}
}

?>