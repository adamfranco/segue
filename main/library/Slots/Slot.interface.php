<?php
/**
 * @since 3/13/08
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Slot.interface.php,v 1.1 2008/03/13 13:29:32 adamfranco Exp $
 */ 

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
 * @version $Id: Slot.interface.php,v 1.1 2008/03/13 13:29:32 adamfranco Exp $
 */
interface Slot {

	/**
	 * @const string $custom;  
	 * @access public
	 * @since 8/14/07
	 */
	const custom = "custom";
	
	/**
	 * @const string $course;  
	 * @access public
	 * @since 8/14/07
	 */
	const course = "course";
	
	/**
	 * @const string $personal;  
	 * @access public
	 * @since 8/14/07
	 */
	const personal = "personal";
	
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
	public static function getExternalSlotDefinitionsForUser ();
	
	/**
	 * Answer an array of allowed location categories.
	 *
	 * @return array of strings
	 * @access public
	 * @since 12/7/07
	 * @static
	 */
	public static function getLocationCategories ();

/*********************************************************
 * Instance Methods
 *********************************************************/
 	
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
	public function getType ();
	
	/**
	 * Given an internal definition of the slot, load any extra owners
	 * that might be in an external data source.
	 * 
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
	public function mergeWithExternal ();
	
	/**
	 * Merge this slot with one defined internally to the system.
	 * Any updates to the internal storage will be made based on the external data.
	 * 
	 * @param object Slot $intSlot
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
	public function mergeWithInternal ( Slot $intSlot );
	
	/**
	 * Answer true if the agent id is an owner of this slot
	 * 
	 * @param object Id $ownerId
	 * @return boolean
	 * @access public
	 * @since 8/14/07
	 */
	public function isOwner ($ownerId);
	
	/**
	 * Answer true if the current user is an owner
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/22/07
	 */
	public function isUserOwner ();

	/**
	 * Answer the shortname of this slot
	 * 
	 * @return string
	 * @access public
	 * @since 7/30/07
	 */
	public function getShortname ();
	
	/**
	 * Answer true if this slot has an existing site.
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/30/07
	 */
	public function siteExists ();
	
	/**
	 * Answer the site id
	 * 
	 * @return object Id
	 * @access public
	 * @since 7/30/07
	 */
	public function getSiteId ();
	
	/**
	 * Answer the Id objects of the owners of this slot
	 * 
	 * @return array
	 * @access public
	 * @since 7/30/07
	 */
	public function getOwners ();
	
	/**
	 * Add a new owner
	 * 
	 * @param object Id $ownerId
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
	public function addOwner ( Id $ownerId );
	
	/**
	 * Remove an existing owner
	 * 
	 * @param object Id $ownerId
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
	public function removeOwner ( $ownerId );
	
	/**
	 * Set the site id
	 * 
	 * @param object Id $siteId
	 * @return void
	 * @access public
	 * @since 8/22/07
	 */
	public function setSiteId ( Id $siteId );
	
	/**
	 * Delete the site id
	 * 
	 * @return void
	 * @access public
	 * @since 8/22/07
	 */
	public function deleteSiteId ();
	
	/**
	 * Answer the Site for this slot
	 * 
	 * @return object Asset
	 * @access public
	 * @since 8/23/07
	 */
	public function getSiteAsset ();
	
	
	/**
	 * Answer the display location for the slot. This can be one of the allowed categories.
	 * 
	 * @return string
	 * @access public
	 * @since 12/6/07
	 */
	public function getLocationCategory ();
	
	/**
	 * Set the display location for the slot. This can be one of the allowed categories.
	 * 
	 * @param string $locationCategory
	 * @return void
	 * @access public
	 * @since 12/6/07
	 */
	public function setLocationCategory ($locationCategory);
	
	/**
	 * Answer the default category for the slot.
	 * 
	 * @return string
	 * @access public
	 * @since 12/6/07
	 */
	public function getDefaultLocationCategory ();
	
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
	public function populateSiteId ( $siteId );
	
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
	public function populateOwnerId ( $ownerId );
	
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
	public function populateRemovedOwnerId ( $ownerId );
	
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
	public function populateLocationCategory ( $locationCategory );
	
}

?>