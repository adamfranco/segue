<?php
/**
 * @since 3/13/08
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1Slot.class.php,v 1.1 2008/03/14 15:39:41 adamfranco Exp $
 */ 

require_once(MYDIR.'/main/library/Slots/Slot.interface.php');

/**
 * The Slot is a placeholder for a Segue site. Slots have an 'alias', a short-name for 
 * identifying a slot and a site, if one has been created. Slots also have an owner.
 * The owner of a slot is the person who is allowed to create a site for the slot.
 * 
 * @since 3/13/08
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1Slot.class.php,v 1.1 2008/03/14 15:39:41 adamfranco Exp $
 */
class Segue1Slot 
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
	public static function getExternalSlotDefinitionsForUser () {
		throw new UnimplementedException;
	}
	
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

/*********************************************************
 * Instance Methods
 *********************************************************/
 	/**
 	 * @var object DOMElement $element;  
 	 * @access private
 	 * @since 3/13/08
 	 */
 	private $element;
 
 	/**
 	 * Constructor.
 	 * 
 	 * @param object DOMElement $element
 	 * @return void
 	 * @access public
 	 * @since 3/13/08
 	 */
 	public function __construct (DOMElement $element) {
 		$this->element = $element;
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
	public function getType () {
		switch ($this->element->getAttribute('type')) {
			case 'personal':
				return 'personal';
			case 'class':
			case 'course':
				return 'course';
			default:
				return 'custom';
		}
	}
	
	/**
	 * Given an internal definition of the slot, load any extra owners
	 * that might be in an external data source.
	 * 
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
	public function mergeWithExternal () {
		throw new UnimplementedException;
	}
	
	/**
	 * Merge this slot with one defined internally to the system.
	 * Any updates to the internal storage will be made based on the external data.
	 * 
	 * @param object Slot $intSlot
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
	public function mergeWithInternal ( Slot $intSlot ) {
		throw new UnimplementedException;
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
		throw new UnimplementedException;
	}
	
	/**
	 * Answer true if the current user is an owner
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/22/07
	 */
	public function isUserOwner () {
		throw new UnimplementedException;
	}

	/**
	 * Answer the shortname of this slot
	 * 
	 * @return string
	 * @access public
	 * @since 7/30/07
	 */
	public function getShortname () {
		return $this->element->getAttribute('shortname');
	}
	
	/**
	 * Answer true if this slot has an existing site.
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/30/07
	 */
	public function siteExists () {
		if (is_null($this->getSiteId()))
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
		if ($this->element->hasAttribute('siteExists') 
			&& $this->element->getAttribute('siteExists') == 'true')
		{
			$idMgr = Services::getService('Id');
			return $idMgr->getId($this->getShortname());
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
		throw new UnimplementedException;
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
		throw new UnimplementedException;
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
		throw new UnimplementedException;
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
		throw new UnimplementedException;
	}
	
	/**
	 * Delete the site id
	 * 
	 * @return void
	 * @access public
	 * @since 8/22/07
	 */
	public function deleteSiteId () {
		throw new UnimplementedException;
	}
	
	/**
	 * Answer the Site for this slot
	 * 
	 * @return object Asset
	 * @access public
	 * @since 8/23/07
	 */
	public function getSiteAsset () {
		if (!isset($this->asset))
			$this->asset = new Segue1SiteAsset(
				$this->element->getElementsByTagName('site')->item(0));
		
		return $this->asset;
	}
	
	/**
	 * @var object Segue1SiteAsset $asset;  
	 * @access private
	 * @since 3/13/08
	 */
	private $asset;
	
	/**
	 * Answer the display location for the slot. This can be one of the allowed categories.
	 * 
	 * @return string
	 * @access public
	 * @since 12/6/07
	 */
	public function getLocationCategory () {
		throw new UnimplementedException;
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
		throw new UnimplementedException;
	}
	
	/**
	 * Answer the default category for the slot.
	 * 
	 * @return string
	 * @access public
	 * @since 12/6/07
	 */
	public function getDefaultLocationCategory () {
		throw new UnimplementedException;
	}
	
/*********************************************************
 * Package Methods:
 * 
 * The following methods are to be used interally to the
 * slot package and not by clients.
 *********************************************************/
	
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
		throw new UnimplementedException;
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
		throw new UnimplementedException;
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
		throw new UnimplementedException;
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
		throw new UnimplementedException;
	}
	
}

/**
 * This is a simple Asset for returning information about a Segue 1 site
 * 
 * @since 3/13/08
 * @package segue.dataport
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Segue1Slot.class.php,v 1.1 2008/03/14 15:39:41 adamfranco Exp $
 */
class Segue1SiteAsset 
	implements Asset
{
	
	/**
	 * @var object DOMElement $element;  
	 * @access private
	 * @since 3/13/08
	 */
	private $element;
	
	/**
	 * Constructor
	 * 
	 * @param object DOMElement $element
	 * @return void
	 * @access public
	 * @since 3/13/08
	 */
	public function __construct (DOMElement $element) {
		$this->element = $element;
		$this->xpath = new DOMXPath($element->ownerDocument);
	}
	
	
	/**
     * Update the display name for this Asset.
     * 
     * @param string $displayName
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}
     * 
     * @access public
     */
    public function updateDisplayName ( $displayName ) {
		throw new UnimplementedException;
	}

    /**
     * Update the date at which this Asset is effective.
     * 
     * @param int $effectiveDate
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#EFFECTIVE_PRECEDE_EXPIRATION}
     * 
     * @access public
     */
    public function updateEffectiveDate ( $effectiveDate ) {
		throw new UnimplementedException;
	}

    /**
     * Update the date at which this Asset expires.
     * 
     * @param int $expirationDate
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#EFFECTIVE_PRECEDE_EXPIRATION}
     * 
     * @access public
     */
    public function updateExpirationDate ( $expirationDate ) {
		throw new UnimplementedException;
	}

    /**
     * Get the display name for this Asset.
     *  
     * @return string
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getDisplayName () {
		$textNodes = $this->xpath->evaluate('./title/text()', $this->element);
		$text = '';
		foreach ($textNodes as $node)
			$text .= $node->nodeValue;
		
		return $text;
	}

    /**
     * Get the description for this Asset.
     *  
     * @return string
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getDescription () {
		$textNodes = $this->xpath->evaluate('./description/text()', $this->element);
		$text = '';
		foreach ($textNodes as $node)
			$text .= $node->nodeValue;
		
		return $text;
	}

    /**
     * Get the unique Id for this Asset.
     *  
     * @return object Id
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getId () {
		throw new UnimplementedException;
	}

    /**
     * Get the AssetType of this Asset.  AssetTypes are used to categorize
     * Assets.
     *  
     * @return object Type
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getAssetType () {
		throw new UnimplementedException;
	}

    /**
     * Get the date at which this Asset is effective.
     *  
     * @return int
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getEffectiveDate () {
		throw new UnimplementedException;
	}

    /**
     * Get the date at which this Asset expires.
     *  
     * @return int
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getExpirationDate () {
		throw new UnimplementedException;
	}

    /**
     * Update the description for this Asset.
     * 
     * @param string $description
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}
     * 
     * @access public
     */
    public function updateDescription ( $description ) {
		throw new UnimplementedException;
	}

    /**
     * Get the Id of the Repository in which this Asset resides.  This is set
     * by the Repository's createAsset method.
     *  
     * @return object Id
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getRepository () {
		throw new UnimplementedException;
	}

    /**
     * Get an Asset's content.  This method can be a convenience if one is not
     * interested in all the structure of the Records.
     *  
     * @return object mixed (original type: java.io.Serializable)
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getContent () {
		throw new UnimplementedException;
	}

    /**
     * Update an Asset's content.
     * 
     * @param object mixed $content (original type: java.io.Serializable)
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}
     * 
     * @access public
     */
    public function updateContent ( $content ) {
		throw new UnimplementedException;
	}

    /**
     * Add an Asset to this Asset.
     * 
     * @param object Id $assetId
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID},
     *         {@link org.osid.repository.RepositoryException#ALREADY_ADDED
     *         ALREADY_ADDED}
     * 
     * @access public
     */
    public function addAsset ( Id $assetId ) {
		throw new UnimplementedException;
	}

    /**
     * Remove an Asset from this Asset.  This method does not delete the Asset
     * from the Repository.
     * 
     * @param object Id $assetId
     * @param boolean $includeChildren
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID}
     * 
     * @access public
     */
    public function removeAsset ( Id $assetId, $includeChildren ) {
		throw new UnimplementedException;
	}

    /**
     * Get all the Assets in this Asset.  Iterators return a set, one at a
     * time.
     *  
     * @return object AssetIterator
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getAssets () {
		throw new UnimplementedException;
	}

    /**
     * Get all the Assets of the specified AssetType in this Repository.
     * Iterators return a set, one at a time.
     * 
     * @param object Type $assetType
     *  
     * @return object AssetIterator
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_TYPE
     *         UNKNOWN_TYPE}
     * 
     * @access public
     */
    public function getAssetsByType ( Type $assetType ) {
		throw new UnimplementedException;
	}

    /**
     * Create a new Asset Record of the specified RecordStructure.   The
     * implementation of this method sets the Id for the new object.
     * 
     * @param object Id $recordStructureId
     *  
     * @return object Record
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID}
     * 
     * @access public
     */
    public function createRecord ( Id $recordStructureId ) {
		throw new UnimplementedException;
	}

    /**
     * Add the specified RecordStructure and all the related Records from the
     * specified asset.  The current and future content of the specified
     * Record is synchronized automatically.
     * 
     * @param object Id $assetId
     * @param object Id $recordStructureId
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID},
     *         {@link
     *         org.osid.repository.RepositoryException#ALREADY_INHERITING_STRUCTURE
     *         ALREADY_INHERITING_STRUCTURE}
     * 
     * @access public
     */
    public function inheritRecordStructure ( Id $assetId, Id $recordStructureId ) {
		throw new UnimplementedException;
	}

    /**
     * Add the specified RecordStructure and all the related Records from the
     * specified asset.
     * 
     * @param object Id $assetId
     * @param object Id $recordStructureId
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID},
     *         {@link
     *         org.osid.repository.RepositoryException#CANNOT_COPY_OR_INHERIT_SELF
     *         CANNOT_COPY_OR_INHERIT_SELF}
     * 
     * @access public
     */
    public function copyRecordStructure ( Id $assetId, Id $recordStructureId ) {
		throw new UnimplementedException;
	}

    /**
     * Delete a Record.  If the specified Record has content that is inherited
     * by other Records, those other Records will not be deleted, but they
     * will no longer have a source from which to inherit value changes.
     * 
     * @param object Id $recordId
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID}
     * 
     * @access public
     */
    public function deleteRecord ( Id $recordId ) {
		throw new UnimplementedException;
	}

    /**
     * Get all the Records for this Asset.  Iterators return a set, one at a
     * time.
     *  
     * @return object RecordIterator
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getRecords () {
		throw new UnimplementedException;
	}

    /**
     * Get all the Records of the specified RecordStructure for this Asset.
     * Iterators return a set, one at a time.
     * 
     * @param object Id $recordStructureId
     *  
     * @return object RecordIterator
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID}
     * 
     * @access public
     */
    public function getRecordsByRecordStructure ( Id $recordStructureId ) {
		throw new UnimplementedException;
	}

    /**
     * Get all the Records of the specified RecordStructureType for this Asset.
     * Iterators return a set, one at a time.
     * 
     * @param object Type $recordStructureType
     *  
     * @return object RecordIterator
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID}
     * 
     * @access public
     */
    public function getRecordsByRecordStructureType ( Type $recordStructureType ) {
		throw new UnimplementedException;
	}

    /**
     * Get all the RecordStructures for this Asset.  RecordStructures are used
     * to categorize information about Assets.  Iterators return a set, one at
     * a time.
     *  
     * @return object RecordStructureIterator
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getRecordStructures () {
		throw new UnimplementedException;
	}

    /**
     * Get the RecordStructure associated with this Asset's content.
     *  
     * @return object RecordStructure
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}
     * 
     * @access public
     */
    public function getContentRecordStructure () {
		throw new UnimplementedException;
	}

    /**
     * Get the Record for this Asset that matches this Record's unique Id.
     * 
     * @param object Id $recordId
     *  
     * @return object Record
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID}
     * 
     * @access public
     */
    public function getRecord ( Id $recordId ) {
		throw new UnimplementedException;
	}

    /**
     * Get the Part for a Record for this Asset that matches this Part's unique
     * Id.
     * 
     * @param object Id $partId
     *  
     * @return object Part
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID}
     * 
     * @access public
     */
    public function getPart ( Id $partId ) {
		throw new UnimplementedException;
	}

    /**
     * Get the Value of the Part of the Record for this Asset that matches this
     * Part's unique Id.
     * 
     * @param object Id $partId
     *  
     * @return object mixed (original type: java.io.Serializable)
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID}
     * 
     * @access public
     */
    public function getPartValue ( Id $partId ) {
		throw new UnimplementedException;
	}

    /**
     * Get the Parts of the Records for this Asset that are based on this
     * RecordStructure PartStructure's unique Id.
     * 
     * @param object Id $partStructureId
     *  
     * @return object PartIterator
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID}
     * 
     * @access public
     */
    public function getPartsByPartStructure ( Id $partStructureId ) {
		throw new UnimplementedException;
	}

    /**
     * Get the Values of the Parts of the Records for this Asset that are based
     * on this RecordStructure PartStructure's unique Id.
     * 
     * @param object Id $partStructureId
     *  
     * @return object ObjectIterator
     * 
     * @throws object RepositoryException An exception with one of
     *         the following messages defined in
     *         org.osid.repository.RepositoryException may be thrown: {@link
     *         org.osid.repository.RepositoryException#OPERATION_FAILED
     *         OPERATION_FAILED}, {@link
     *         org.osid.repository.RepositoryException#PERMISSION_DENIED
     *         PERMISSION_DENIED}, {@link
     *         org.osid.repository.RepositoryException#CONFIGURATION_ERROR
     *         CONFIGURATION_ERROR}, {@link
     *         org.osid.repository.RepositoryException#UNIMPLEMENTED
     *         UNIMPLEMENTED}, {@link
     *         org.osid.repository.RepositoryException#NULL_ARGUMENT
     *         NULL_ARGUMENT}, {@link
     *         org.osid.repository.RepositoryException#UNKNOWN_ID UNKNOWN_ID}
     * 
     * @access public
     */
    public function getPartValuesByPartStructure ( Id $partStructureId ) {
		throw new UnimplementedException;
	}

	
}

?>