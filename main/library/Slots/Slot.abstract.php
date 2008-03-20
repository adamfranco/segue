<?php
/**
 * @since 7/27/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Slot.abstract.php,v 1.10 2008/03/20 20:53:01 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/Slot.interface.php");


/**
 * The Slot is a placeholder for a Segue site. Slots have an 'alias', a short-name for 
 * identifying a slot and a site, if one has been created. Slots also have an owner.
 * The owner of a slot is the person who is 
 * 
 * @since 7/27/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Slot.abstract.php,v 1.10 2008/03/20 20:53:01 adamfranco Exp $
 */
abstract class SlotAbstract 
	implements Slot
{
	
/*********************************************************
 * Static Methods
 *********************************************************/
	
	/**
	 * Answer the external slots for the current user
	 * 
	 * @return array
	 * @access protected
	 * @static
	 * @since 8/14/07
	 */
// 	abstract public static function getExternalSlotDefinitionsForUser ();
	
	/**
	 * Answer an array of allowed location categories.
	 *
	 * @return array of strings
	 * @access public
	 * @since 12/7/07
	 * @static
	 */
	public static function getLocationCategories () {
		return array('main', 'community');
	}
	
	/**
	 * Set the default media quota
	 * 
	 * @param object Integer $quota
	 * @return void
	 * @access public
	 * @since 3/20/08
	 * @static
	 */
	public static function setDefaultMediaQuota (Integer $quota) {
		self::$defaultMediaQuota = $quota->value();
	}
	

/*********************************************************
 * Instance variables
 *********************************************************/

	/**
	 * @var string $shortname;  
	 * @access private
	 * @since 7/30/07
	 */
	private $shortname;
	
	/**
	 * @var mixed $siteId;  
	 * @access private
	 * @since 7/30/07
	 */
	private $siteId = null;
	
	/**
	 * @var boolean $isInDB;  
	 * @access private
	 * @since 8/14/07
	 */
	private $isInDB = false;
	
	/**
	 * @var array $owners;  
	 * @access private
	 * @since 7/30/07
	 */
	private $owners = array();
	
	/**
	 * @var array $removedOwners;  
	 * @access private
	 * @since 7/30/07
	 */
	private $removedOwners = array();
	
	/**
	 * @var string $locationCategory; The category for where the slot should be displayed 
	 * @access private
	 * @since 12/6/07
	 */
	private $locationCategory;
	
	/**
	 * @var int $mediaQuota; The quota for media in this slot's site. 10485760 = 10MB
	 * @access private
	 * @since 3/20/08
	 */
	private $mediaQuota = 0;
	
	/**
	 * @var int $defaultMediaQuota; The default quota for media in any slots' site. 10485760 = 10MB 
	 * @access private
	 * @since 3/20/08
	 * @static
	 */
	private static $defaultMediaQuota = 10485760;
	
/*********************************************************
 * Instance Methods
 *********************************************************/

	/**
	 * Constructor
	 * 
	 * @param string $shortname
	 * @return void
	 * @access public
	 * @since 7/30/07
	 */
	function __construct ( $shortname, $fromDB = false ) {
		$this->shortname = $shortname;
		$this->owners = array();
		$this->removedOwners = array();
		$this->isInDB = $fromDB;
		
		$this->mediaQuota = self::$defaultMediaQuota;
	}
	
	/**
	 * Answer the type of slot for this instance. The type of slot corresponds to
	 * how it is populated/originated. Some slots are originated programatically,
	 * others are added manually. The type should not be used for classifying where
	 * as site should be displayed. Use the location category for that.
	 * 
	 * @return string
	 * @access public
	 * @since 8/14/07
	 */
// 	abstract public function getType ();
	
	/**
	 * Given an internal definition of the slot, load any extra owners
	 * that might be in an external data source.
	 * 
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
// 	abstract public function mergeWithExternal ();
	
	/**
	 * Merge this slot with one defined internally to the system.
	 * Any updates to the internal storage will be made based on the external data.
	 * 
	 * @param object Slot $intSlot
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
	public final function mergeWithInternal ( Slot $intSlot ) {
		if ($this->getShortname() != $intSlot->getShortname())
			throw new Exception("Cannot merge slots with differing shortnames. '".$this->getShortname()."' != '".$intSlot->getShortname()."'");
		
		$this->siteId = $intSlot->getSiteId();
		
		foreach ($this->getOwners() as $key => $ownerId) {
			// If this owner was intentionally removed, don't list them.
			if ($intSlot->isRemovedOwner($ownerId)) {
				unset($this->owners[$key]);
			}
			// If this owner only appears in the external definition, add it to
			// the internal definition.
			else if (!$intSlot->isOwner($ownerId)) {
				$this->addOwner($ownerId);
			}
			// Otherwise, the owner appears as valid in both definitions and
			// can be ignored for the purposes of merging.
		}
		
		// Add owners that only appear in the internal slot
		foreach ($intSlot->getOwners() as $ownerId) {
			// If this owner was intentionally removed, populate that
			if (!$this->isOwner($ownerId)) {
				$this->populateOwnerId($ownerId);
			}
			// Otherwise, the owner appears as valid in both definitions and
			// can be ignored for the purposes of merging.
		}
		
		// Add in "Removed owners" that only appear in the internal slot
		foreach ($intSlot->getRemovedOwners() as $ownerId) {
			// If this owner was intentionally removed, populate that
			if (!$this->isRemovedOwner($ownerId)) {
				$this->populateRemovedOwnerId($ownerId);
			}
			// Otherwise, the owner appears as valid in both definitions and
			// can be ignored for the purposes of merging.
		}
		
		$this->isInDB = true;
	}
	
	/**
	 * Answer true if the owner id was removed manually.
	 * 
	 * @param object Id $ownerId
	 * @return boolean
	 * @access private
	 * @since 8/14/07
	 */
	protected final function isRemovedOwner ($ownerId) {
		foreach ($this->removedOwners as $removedId) {
			if ($removedId->isEqual($ownerId))
				return true;
		}
		
		return false;
	}
	
	/**
	 * Answer true if the agent id is an owner of this slot
	 * 
	 * @param object Id $ownerId
	 * @return boolean
	 * @access public
	 * @since 8/14/07
	 */
	public function isOwner ($ownerId) {
		foreach ($this->getOwners() as $id) {
			if ($id->isEqual($ownerId))
				return true;
		}
		
		return false;
	}
	
	/**
	 * Answer true if the current user is an owner
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/22/07
	 */
	public function isUserOwner () {
		$authN = Services::getService("AuthN");
		return $this->isOwner($authN->getFirstUserId());
	}
	
	/**
	 * Answer true if the current user is a removed owner.
	 *
	 * @return boolean
	 * @access protected
	 * @since 1/4/08
	 */
	protected function isUserRemovedOwner () {
		$authN = Services::getService("AuthN");
		return $this->isRemovedOwner($authN->getFirstUserId());
	}
	

	/**
	 * Answer the shortname of this slot
	 * 
	 * @return string
	 * @access public
	 * @since 7/30/07
	 */
	public function getShortname () {
		return $this->shortname;
	}
	
	/**
	 * Answer true if this slot has an existing site.
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/30/07
	 */
	public function siteExists () {
		if (is_null($this->siteId)) 
			return false;
		else
			return true;
	}
	
	/**
	 * Answer the site id
	 * 
	 * @return object Id
	 * @access public
	 * @since 7/30/07
	 */
	public function getSiteId () {
		if (!is_null($this->siteId)) {
			return $this->siteId;		
		}
		
		return null;
	}
	
	/**
	 * Answer the Id objects of the owners of this slot
	 * 
	 * @return array
	 * @access public
	 * @since 7/30/07
	 */
	public function getOwners () {
		return $this->owners;
	}
	
	/**
	 * Answer the owners of this slot that have been manually removed.
	 * 
	 * @return array
	 * @access private
	 * @since 7/30/07
	 */
	private function getRemovedOwners () {
		return $this->removedOwners;
	}
	
	/**
	 * Add a new owner
	 * 
	 * @param object Id $ownerId
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
	public function addOwner ( Id $ownerId ) {
		if (!$this->isOwner($ownerId)) {
			$this->recordInDB();
			if ($this->isOwner($ownerId) || $this->isRemovedOwner($ownerId)) {
				$query = new UpdateQuery;
				$query->addWhereEqual('owner_id', $ownerId->getIdString());
			} else {
				$query = new InsertQuery;
				$query->addValue('owner_id', $ownerId->getIdString());
				$query->addValue('shortname', $this->getShortname());
			}		
			$query->setTable('segue_slot_owner');
			$query->addValue('removed', '0');
			
			$dbc = Services::getService('DBHandler');
			$result = $dbc->query($query, IMPORTER_CONNECTION);
			
			if ($this->isRemovedOwner($ownerId)) {
				foreach ($this->removedOwners as $key => $id) {
					if ($id->isEqual($ownerId))
						unset($this->removedOwners[$key]);
				}
			}
			$this->owners[] = $ownerId;
		}
	}
	
	/**
	 * Remove an existing owner
	 * 
	 * @param object Id $ownerId
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
	public function removeOwner ( $ownerId ) {
		if (!$this->isRemovedOwner($ownerId)) {
			$this->recordInDB();
			
			$query = new UpdateQuery;
			$query->setTable('segue_slot_owner');
			$query->addWhereEqual('shortname', $this->getShortname());
			$query->addWhereEqual('owner_id', $ownerId->getIdString());
			$query->addValue('removed', '1');
			
			$dbc = Services::getService('DBHandler');
			$dbc->query($query, IMPORTER_CONNECTION);
			
			if ($this->isOwner($ownerId)) {
				foreach ($this->getOwners() as $key => $id) {
					if ($id->isEqual($ownerId))
						unset($this->owners[$key]);
				}
			}
			
			$this->removedOwners[] = $ownerId;
		}
	}
	
	/**
	 * Set the site id
	 * 
	 * @param object Id $siteId
	 * @return void
	 * @access public
	 * @since 8/22/07
	 */
	public function setSiteId ( Id $siteId ) {
		$this->recordInDB();
			
		$query = new UpdateQuery;
		$query->setTable('segue_slot');
		$query->addWhereEqual('shortname', $this->getShortname());
		$query->addValue('site_id', $siteId->getIdString());
		
		$dbc = Services::getService('DBHandler');
		$dbc->query($query, IMPORTER_CONNECTION);
	}
	
	/**
	 * Delete the site id
	 * 
	 * @return void
	 * @access public
	 * @since 8/22/07
	 */
	public function deleteSiteId () {
		$this->recordInDB();
			
		$query = new UpdateQuery;
		$query->setTable('segue_slot');
		$query->addWhereEqual('shortname', $this->getShortname());
		$query->addRawValue('site_id', 'NULL');
		
		$dbc = Services::getService('DBHandler');
		$dbc->query($query, IMPORTER_CONNECTION);
	}
	
	/**
	 * Answer the Site for this slot
	 * 
	 * @return object Asset
	 * @access public
	 * @since 8/23/07
	 */
	public function getSiteAsset () {
		if (is_null($this->getSiteId()))
			throw new Exception("Cannot get a site Asset for a slot that doesn't have a siteId set ('".$this->getShortname()."').");
		
		$repositoryManager = Services::getService("Repository");
		$idManager = Services::getService("Id");
		$repository = $repositoryManager->getRepository(
				$idManager->getId('edu.middlebury.segue.sites_repository'));
		
		return $repository->getAsset($this->getSiteId());
	}
	
	/**
	 * Store a siteId in this object, does not update the database. 
	 * This method is internal to this package and should not be used
	 * by clients.
	 * 
	 * @param mixed siteId string or Id object
	 * @return void
	 * @access public
	 * @since 7/30/07
	 */
	public function populateSiteId ( $siteId ) {
		if (is_null($siteId))
			return;
			
		if (is_object($siteId))
			$this->siteId = $siteId;
		else {
			$idManager = Services::getService("Id");
			$this->siteId = $idManager->getId($siteId);
		}
	}
	
	/**
	 * Add a site owner in this object, does not update the database. 
	 * This method is internal to this package and should not be used
	 * by clients.
	 * 
	 * @param mixed siteId string or Id object
	 * @return void
	 * @access public
	 * @since 7/30/07
	 */
	public function populateOwnerId ( $ownerId ) {
		if (is_null($ownerId))
			return;
			
		if (is_object($ownerId))
			$this->owners[] = $ownerId;
		else {
			$idManager = Services::getService("Id");
			$this->owners[] = $idManager->getId($ownerId);
		}
	}
	
	/**
	 * Add a "removed" site owner in this object, does not update the database. 
	 * This method is internal to this package and should not be used
	 * by clients.
	 * 
	 * @param mixed siteId string or Id object
	 * @return void
	 * @access public
	 * @since 7/30/07
	 */
	public function populateRemovedOwnerId ( $ownerId ) {
		if (is_null($ownerId))
			return;
			
		if (is_object($ownerId))
			$this->removedOwners[] = $ownerId;
		else {
			$idManager = Services::getService("Id");
			$this->removedOwners[] = $idManager->getId($ownerId);
		}
	}
	
	/**
	 * Answer the display location for the slot. This can be one of the allowed categories.
	 * 
	 * @return string
	 * @access public
	 * @since 12/6/07
	 */
	public function getLocationCategory () {
		if (isset($this->locationCategory))
			return $this->locationCategory;
		else
			return $this->getDefaultLocationCategory();
	}
	
	/**
	 * Set the display location for the slot. This can be one of the allowed categories.
	 * 
	 * @param string $locationCategory
	 * @return void
	 * @access public
	 * @since 12/6/07
	 */
	public function setLocationCategory ($locationCategory) {
		if (!in_array($locationCategory, self::getLocationCategories()))
			throw new Exception("Invalid category, '$locationCategory'.");
		
		$this->locationCategory = $locationCategory;
		
		$this->recordInDB();
			
		$query = new UpdateQuery;
		$query->setTable('segue_slot');
		$query->addWhereEqual('shortname', $this->getShortname());
		$query->addValue('location_category', $this->locationCategory);
		
		$dbc = Services::getService('DBHandler');
		$dbc->query($query, IMPORTER_CONNECTION);
	}
	
	/**
	 * Add the location category to this object, does not update the database. 
	 * This method is internal to this package and should not be used
	 * by clients.
	 * 
	 * @param string $locationCategory
	 * @return void
	 * @access public
	 * @since 12/6/07
	 */
	public function populateLocationCategory ( $locationCategory ) {
		if (!in_array($locationCategory, self::getLocationCategories()))
			throw new Exception("Invalid category, '$locationCategory'.");
		
		$this->locationCategory = $locationCategory;
	}
	
	/**
	 * Answer the default category for the slot.
	 * 
	 * @return string
	 * @access public
	 * @since 12/6/07
	 */
// 	abstract public function getDefaultLocationCategory ();
	
	/**
	 * Record an entry for the slot in the local database
	 * 
	 * @return void
	 * @access private
	 * @since 8/14/07
	 */
	private function recordInDB () {
		if (!$this->isInDB) {
			$dbc = Services::getService('DBHandler');
			
			try {
				// Add a row to the slot table
				$query = new InsertQuery;
				$query->setTable('segue_slot');
				$query->addValue('shortname', $this->getShortname());
				if ($this->getSiteId())
					$query->addValue('site_id', $this->getSiteId()->getIdString());
				$query->addValue('type', $this->getType());
				$query->addValue('location_category', $this->getLocationCategory());
				if ($this->mediaQuota == self::$defaultMediaQuota)
					$query->addRawValue('media_quota', 'null');
				else
					$query->addValue('media_quota', $this->mediaQuota);
				$dbc->query($query, IMPORTER_CONNECTION);
			} catch (DuplucateKeyDatabaseException $e) {
				// Update row to the slot table
				$query = new UpdateQuery;
				$query->setTable('segue_slot');
				$query->addWhereEqual('shortname', $this->getShortname());
				if ($this->getSiteId())
					$query->addValue('site_id', $this->getSiteId()->getIdString());
				$query->addValue('type', $this->getType());
				$query->addValue('location_category', $this->getLocationCategory());
				if ($this->mediaQuota == self::$defaultMediaQuota)
					$query->addRawValue('media_quota', 'null');
				else
					$query->addValue('media_quota', $this->mediaQuota);
				
				$dbc->query($query, IMPORTER_CONNECTION);
			}
			
			// Add existing owners to the slot_owner table
			// Adam 2007-08-16: Not sure if we actually need to do this...
			if (count($this->getOwners())) {
				$query = new InsertQuery;
				$query->setTable('segue_slot_owner');
				foreach($this->getOwners() as $ownerId) {
					$query->addValue('shortname', $this->getShortname());
					$query->addValue('owner_id', $ownerId->getIdString());
					try {
						$dbc->query($query, IMPORTER_CONNECTION);
					} catch (DuplucateKeyDatabaseException $e) {
						// If already there, just skip.
					}
				}
			}
			
			$this->isInDB = true;
		}
	}
	
	/**
	 * Answer the media library quota
	 * 
	 * @return object ByteSize
	 * @access public
	 * @since 3/20/08
	 */
	public function getMediaQuota () {
		return ByteSize::withValue($this->mediaQuota);
	}
	
	/**
	 * Set the media library quota
	 * 
	 * @param object Integer $quota
	 * @return void
	 * @access public
	 * @since 3/20/08
	 */
	public function setMediaQuota (Integer $quota) {
		$this->mediaQuota = $quota->value();
		
		$this->recordInDB();
			
		$query = new UpdateQuery;
		$query->setTable('segue_slot');
		$query->addWhereEqual('shortname', $this->getShortname());
		$query->addValue('media_quota', $this->mediaQuota);
		
		$dbc = Services::getService('DBHandler');
		$dbc->query($query, IMPORTER_CONNECTION);
	}
	
	/**
	 * Set the media library quota to be the default
	 * 
	 * @return void
	 * @access public
	 * @since 3/20/08
	 */
	public function useDefaultMediaQuota () {
		$this->mediaQuota = self::$defaultMediaQuota;
		
		$this->recordInDB();
			
		$query = new UpdateQuery;
		$query->setTable('segue_slot');
		$query->addWhereEqual('shortname', $this->getShortname());
		$query->addRawValue('media_quota', 'NULL');
		
		$dbc = Services::getService('DBHandler');
		$dbc->query($query, IMPORTER_CONNECTION);
	}
}

?>