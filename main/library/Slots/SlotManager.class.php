<?php
/**
 * @since 8/14/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SlotManager.class.php,v 1.14 2008/04/08 19:43:24 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/CustomSlot.class.php");
require_once(dirname(__FILE__)."/PersonalSlot.class.php");
require_once(dirname(__FILE__)."/CourseSlot.class.php");
require_once(dirname(__FILE__)."/AllSlotsIterator.class.php");


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
 * @version $Id: SlotManager.class.php,v 1.14 2008/04/08 19:43:24 adamfranco Exp $
 */
class SlotManager {
		
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
			self::$instance = new SlotManager;
		
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
			throw new InvalidArgumentException ("Unknown SlotType, $slotType.");
		}
		
		// Each time a slot is requested by name, but doesn't exist this method gets
		// run. This is particularly the case in the Segue1to2 migration screens.
		// By caching these results for the execution cycle, this process can be
		// greatly speeded.
		if (!isset($this->slotsByTypeCache))
			$this->slotsByTypeCache = array();
		
		if (!isset($this->slotsByTypeCache[$slotType])) {
			$this->slotsByTypeCache[$slotType] = array();
		
			$slotClass = $this->slotTypes[$slotType];
			
			eval('$extSlots = '.$slotClass.'::getExternalSlotDefinitionsForUser();');
			$extNames = array();
			foreach($extSlots as $slot) {
				$extNames[] = $slot->getShortname();
			}
			
			$intSlots = $this->getInternalSlotDefinitionsForUserByType($slotType);
			$intDefsOfExtSlots = $this->getInternalSlotDefinitionsForShortnames($extNames);
			$intSlots = array_merge($intSlots, $intDefsOfExtSlots);
			
			$slots = $this->mergeSlots($extSlots, $intSlots);
			foreach ($slots as $slot)
				$this->slots[$slot->getShortname()] = $slot;
			
			$this->slotsByTypeCache[$slotType] = $slots;
		}
		return $this->slotsByTypeCache[$slotType];
	}
	
	/**
	 * Answer all slots for the current user
	 * 
	 * @return array
	 * @access public
	 * @since 8/16/07
	 */
	public function getSlots () {
		$slots = array();
		foreach ($this->slotTypes as $type => $classname) {
			$slots = array_merge($slots, $this->getSlotsByType($type));
		}
		
		return $slots;
	}
	
	/**
	 * Answer an iterator of all internally defined slots owned by any agent.
	 * 
	 * @return object Iterator
	 * @access public
	 * @since 12/4/07
	 */
	public function getAllSlots () {
		return new AllSlotsIterator;
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
		ArgumentValidator::validate($shortname, NonzeroLengthStringValidatorRule::getRule());
		$shortname = strtolower($shortname);
		ArgumentValidator::validate($shortname, RegexValidatorRule::getRule('^[a-z0-9\._-]+$'));
		
		if (!isset($this->slots[$shortname])) {
			$this->getSlots();
			
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
		ArgumentValidator::validate($siteId, 
			OrValidatorRule::getRule(
				NonzeroLengthStringValidatorRule::getRule(),
				ExtendsValidatorRule::getRule('Id')));
				
		if (is_object($siteId)) {
			$tmp = $siteId;
			unset($siteId);
			$siteId = $tmp->getIdString();
		}
		// Check our cache
		foreach ($this->slots as $slot) {
			if ($slot->getSiteId() == $siteId && !$slot->isAlias())
				return $slot;
		}
		
		// Look up the slot in the database;
		try {
			$result = $this->getSlotByIdResult_Harmoni_Db($siteId);
		} catch (UnknownIdException $e) {
			$result = $this->getSlotByIdResult($siteId);
		}
		
		if ($result->getNumberOfRows()) {
			$slots = $this->getSlotsFromQueryResult($result);
			if (count($slots) !== 1)
				throw new Exception ("Mismached number of slots.");
			
			$slot = current($slots);
			$this->slots[$slot->getShortname()] = $slot;
		} else {
			throw new UnknownIdException("No Slot Found for site id, '$siteId'");
		}
		
		return $slot;
	}
	
	/**
	 * Answer an array of slots that have sites and whose shortnames match a search string. 
	 * Use '*' as the wildcard.
	 * 
	 * @param string $searchCriteria
	 * @return array
	 * @access public
	 * @since 10/9/08
	 */
	public function getSlotsWithSitesBySearch ($searchCriteria) {
		if (!strlen($searchCriteria))
			return array();
		
		$searchCriteria = str_replace('*', '%', $searchCriteria);
		
		$query = new SelectQuery;
		$query->addTable('segue_slot');
		$query->addTable('segue_slot_owner AS all_owners', LEFT_JOIN, 'segue_slot.shortname = all_owners.shortname');
		
		$query->addColumn('segue_slot.shortname', 'shortname');
		$query->addColumn('segue_slot.site_id', 'site_id');
		$query->addColumn('segue_slot.alias_target', 'alias_target');
		$query->addColumn('segue_slot.type', 'type');
		$query->addColumn('segue_slot.location_category', 'location_category');
		$query->addColumn('segue_slot.media_quota', 'media_quota');
		$query->addColumn('all_owners.owner_id', 'owner_id');
		$query->addColumn('all_owners.removed', 'removed');
		
		$query->addWhereLike('segue_slot.shortname', $searchCriteria);
		
// 		printpre($query->asString());
		$dbc = Services::getService('DBHandler');
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		return $this->getSlotsFromQueryResult($result);
	}
	
	/**
	 * Answer a slot query result for the site id specified
	 * 
	 * @param string $siteId
	 * @return SelectQueryResult or Harmoni_Db_SelectResult
	 * @access private
	 * @since 4/8/08
	 */
	private function getSlotByIdResult ($siteId) {
		// Look up the slot in the database;
		$query = new SelectQuery;
		$query->addTable('segue_slot');
		$query->addTable('segue_slot_owner AS all_owners', LEFT_JOIN, 'segue_slot.shortname = all_owners.shortname');
		
		$query->addColumn('segue_slot.shortname', 'shortname');
		$query->addColumn('segue_slot.site_id', 'site_id');
		$query->addColumn('segue_slot.alias_target', 'alias_target');
		$query->addColumn('segue_slot.type', 'type');
		$query->addColumn('segue_slot.location_category', 'location_category');
		$query->addColumn('segue_slot.media_quota', 'media_quota');
		$query->addColumn('all_owners.owner_id', 'owner_id');
		$query->addColumn('all_owners.removed', 'removed');
		
		$query->addWhereEqual('segue_slot.site_id', $siteId);
		
				
// 		print $query->asString();
		$dbc = Services::getService('DBHandler');
		return $dbc->query($query, IMPORTER_CONNECTION);
	}
	
	/**
	 * Answer a slot query result for the site id specified
	 * 
	 * @param string $siteId
	 * @return SelectQueryResult or Harmoni_Db_SelectResult
	 * @access private
	 * @since 4/8/08
	 */
	private function getSlotByIdResult_Harmoni_Db ($siteId) {
		// Look up the slot in the database;
		if (!isset($this->getSlotBySiteId_stmt)) {
			$query = Harmoni_Db::getDatabase('segue_db')->select();
			$query->addTable('segue_slot');
			$query->addTable('segue_slot_owner AS all_owners', LEFT_JOIN, 'segue_slot.shortname = all_owners.shortname');
			
			$query->addColumn('segue_slot.shortname', 'shortname');
			$query->addColumn('segue_slot.site_id', 'site_id');
			$query->addColumn('segue_slot.alias_target', 'alias_target');
			$query->addColumn('segue_slot.type', 'type');
			$query->addColumn('segue_slot.location_category', 'location_category');
			$query->addColumn('segue_slot.media_quota', 'media_quota');
			$query->addColumn('all_owners.owner_id', 'owner_id');
			$query->addColumn('all_owners.removed', 'removed');
			
			$this->getSlotBySiteId_siteId_key = $query->addWhereEqual('segue_slot.site_id', $siteId);
			
			$this->getSlotBySiteId_stmt = $query->prepare();
		}		
		
		$this->getSlotBySiteId_stmt->bindParam($this->getSlotBySiteId_siteId_key, $siteId);
		$this->getSlotBySiteId_stmt->execute();
		return $this->getSlotBySiteId_stmt->getResult();
	}
	
	/**
	 * Load a number of slots from the database
	 * 
	 * @param array $slotShortnames
	 * @return array of slot objects
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
		
		$slotsToReturn = array();
		if (count($toLoad)) {
		
			// Look up the slot in the database;
			$query = new SelectQuery;
			$query->addTable('segue_slot');
			$query->addTable('segue_slot_owner AS all_owners', LEFT_JOIN, 'segue_slot.shortname = all_owners.shortname');
			
			$query->addColumn('segue_slot.shortname', 'shortname');
			$query->addColumn('segue_slot.site_id', 'site_id');
			$query->addColumn('segue_slot.alias_target', 'alias_target');
			$query->addColumn('segue_slot.type', 'type');
			$query->addColumn('segue_slot.location_category', 'location_category');
			$query->addColumn('segue_slot.media_quota', 'media_quota');
			$query->addColumn('all_owners.owner_id', 'owner_id');
			$query->addColumn('all_owners.removed', 'removed');
			
			$query->addWhereIn('segue_slot.shortname', $toLoad);
			
					
	// 		print $query->asString();
			$dbc = Services::getService('DBHandler');
			$result = $dbc->query($query, IMPORTER_CONNECTION);
			
			$slots = $this->getSlotsFromQueryResult($result);
				
			foreach ($slots as $slot) {
				$this->slots[$slot->getShortname()] = $slot;
				$slotsToReturn[] = $slot;
			}
		}
		
		return $slotsToReturn;
	}
	
	/**
	 * Delete a slot
	 * 
	 * @param string $shortname
	 * @return void
	 * @access public
	 * @since 12/5/07
	 */
	public function deleteSlot ($shortname) {
		$slot = $this->getSlotByShortname($shortname);
		if ($slot->siteExists())
			throw new PermissionDeniedException("Cannot delete a slot for an existing site.");
		
		$query = new DeleteQuery;
		$query->setTable('segue_slot');
		$query->addWhereEqual('shortname', $shortname);
		
		$dbc = Services::getService('DBHandler');
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		$query = new DeleteQuery;
		$query->setTable('segue_slot_owner');
		$query->addWhereEqual('shortname', $shortname);
		
		$dbc = Services::getService('DBHandler');
		$result = $dbc->query($query, IMPORTER_CONNECTION);
		
		unset($this->slots[$shortname]);
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
		$query->addColumn('segue_slot.alias_target', 'alias_target');
		$query->addColumn('segue_slot.type', 'type');
		$query->addColumn('segue_slot.location_category', 'location_category');
		$query->addColumn('segue_slot.media_quota', 'media_quota');
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
	 * Answer the internal slot definitions for the shortnames passed
	 * 
	 * @param array $shortnames An array of strings
	 * @return array
	 * @access private
	 * @since 1/4/08
	 */
	private function getInternalSlotDefinitionsForShortnames (array $shortnames) {
		if (!count($shortnames))
			return array();
		
		$query = new SelectQuery;
		$query->addTable('segue_slot');
		$query->addTable('segue_slot_owner AS all_owners', LEFT_JOIN, 'segue_slot.shortname = all_owners.shortname');
		
		$query->addColumn('segue_slot.shortname', 'shortname');
		$query->addColumn('segue_slot.site_id', 'site_id');
		$query->addColumn('segue_slot.alias_target', 'alias_target');
		$query->addColumn('segue_slot.type', 'type');
		$query->addColumn('segue_slot.location_category', 'location_category');
		$query->addColumn('segue_slot.media_quota', 'media_quota');
		$query->addColumn('all_owners.owner_id', 'owner_id');
		$query->addColumn('all_owners.removed', 'removed');
		
		$query->addWhereIn('segue_slot.shortname', $shortnames);
		
// 		printpre($query->asString());
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
					throw new Exception ("Unknown SlotType, '$slotType'. Should be one of (".implode(", ", array_keys($this->slotTypes)).").");
				
				$slotClass = $this->slotTypes[$slotType];
				$slot = new $slotClass ($result->field('shortname'), true);
				
				// Add site ids from DB if it exists
				if ($result->field('site_id') !== '')
					$slot->populateSiteId($result->field('site_id'));
				
				// Add the alias target
				else if ($result->field('alias_target') !== '' 
						&& !is_null($result->field('alias_target')))
					$slot->populateAlias($result->field('alias_target'));
					
				// Add location category from DB if it exists
				if ($result->field('location_category') !== '')
					$slot->populateLocationCategory($result->field('location_category'));
				
				if (is_numeric($result->field('media_quota')))
					$slot->populateMediaQuota(intval($result->field('media_quota')));
				
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
// 			printpre("Adding External ".$extSlot->getShortname());
			if (isset($intSlots[$extSlot->getShortname()])) {
// 				printpre("\tMerging with internal");
				$extSlot->mergeWithInternal($intSlots[$extSlot->getShortname()]);
				unset($intSlots[$extSlot->getShortname()]);
			} else {
				// check for an internal definition and merge with that.
				
			}
			$slots[$extSlot->getShortname()] = $extSlot;
		}
		
		foreach ($intSlots as $intSlot) {
// 			printpre("Adding Internal ".$intSlot->getShortname());
			if (!isset($slots[$intSlot->getShortname()]))
				$slots[$intSlot->getShortname()] = $intSlot;
		}
		
		ksort($slots);
		
		return $slots;
	}
	
	/**
	 * Convert a slot to another type. The object passed on will no longer be valid.
	 * 
	 * @param object Slot $slot
	 * @param string $type
	 * @return object Slot
	 * @access public
	 * @since 1/4/08
	 */
	public function convertSlotToType (Slot $slot, $type) {
		if (!isset($this->slotTypes[$type]))
			throw new Exception ("Unknown SlotType, '$type'. Should be one of (".implode(", ", array_keys($this->slotTypes)).").");
		
		$shortname = $slot->getShortname();
		$dbc = Services::getService("DatabaseManager");
		try {
			// Add a row to the slot table
			$query = new InsertQuery;
			$query->setTable('segue_slot');
			$query->addValue('shortname', $shortname);
			if ($slot->getSiteId())
				$query->addValue('site_id', $slot->getSiteId()->getIdString());
			if ($slot->isAlias())
				$query->addValue('alias_target', $slot->getAliasTarget()->getShortname());
			$query->addValue('type', $type);
			$query->addValue('location_category', $slot->getLocationCategory());
			if (!$slot->usesDefaultMediaQuota())
				$query->addValue('media_quota', $slot->getMediaQuota());
						
			$dbc->query($query, IMPORTER_CONNECTION);
		} catch (DuplicateKeyDatabaseException $e) {
			// Update row to the slot table
			$query = new UpdateQuery;
			$query->setTable('segue_slot');
			$query->addWhereEqual('shortname', $shortname);
			$query->addValue('type', $type);
			
			$dbc->query($query, IMPORTER_CONNECTION);
		}
		
		// Clear our cache
		unset($this->slots[$shortname]);
		
		$slot = $this->getSlotByShortname($shortname);
		return $slot;
	}
}

?>