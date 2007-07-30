<?php
/**
 * @since 7/25/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SlotManager.class.php,v 1.1 2007/07/30 18:13:40 adamfranco Exp $
 */ 

require_once(dirname(__FILE__).'/Slot.class.php');

/**
 * The Slot manager handles creating and accessing Slots. Slots are the placeholders 
 * for sites that can be created and maintain information such as a shortname
 * for the site, the owner (responsible party) for the site, and the type of the 
 * site (class, personal, other).
 * 
 * @since 7/25/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SlotManager.class.php,v 1.1 2007/07/30 18:13:40 adamfranco Exp $
 */
class SlotManager {
		
	/**
	 * Get the instance of the object.
	 * This class implements the Singleton pattern. There is only ever
	 * one instance of the object and it is accessed only via the 
	 * SlotManager::instance() method.
	 * 
	 * @return object Harmoni
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	function &instance () {
		if (!defined("SLOT_MANAGER_INSTANTIATED")) {
			$GLOBALS['__slotManager'] =& new SlotManager();
			define("SLOT_MANAGER_INSTANTIATED", true);
		}
		
		return $GLOBALS['__slotManager'];
	}
	
	/*********************************************************
	 * The following methods support working with site shortnames.
	 * Shortnames are syntactically-meaningful user-specified 
	 * identifiers for sites. Shortnames are only guarenteed to be
	 * unique within the scope of a given segue installation.
	 *
	 * Only site nodes can have shortnames.
	 *********************************************************/
	
	/**
	 * Answer the shortname for a site id if one exists or null if not found.
	 * 
	 * @param object Id $id
	 * @return object Slot OR null
	 * @access public
	 * @since 7/25/07
	 */
	function &getSlotForSiteId ( $id ) {
		$query =& new SelectQuery;
		$query->addTable('segue_slot');
		$query->addTable('segue_slot_owner', LEFT_JOIN, 'segue_slot_owner.shortname = segue_slot.shortname');
		$query->addColumn('segue_slot.shortname', 'shortname');
		$query->addColumn('site_id');
		$query->addColumn('owner_id');
		$query->addWhereEqual('site_id', $id->getIdString());
		
		$dbc =& Services::getService('DBHandler');
		$result =& $dbc->query($query, IMPORTER_CONNECTION);
		if ($result->getNumberOfRows()) {
			if ($result->field('shortname') !== '') {
				$slot = new Slot ($result->field('shortname'));
				
				// Add site ids from DB if it exists
				if ($result->field('site_id') !== '')
					$slot->populateSiteId($result->field('site_id'));
				
				
				while($result->hasMoreRows()) {
					if ($result->field('owner_id') !== '')
						$slot->populateOwnerId($result->field('owner_id'));
					$result->advanceRow();
				}
				
				return $slot;
			}
		}
		
		$null = null;
		return $null;
	}
	
	/**
	 * Answer the site id for an shortname or null if not found
	 * 
	 * @param string $shortname
	 * @return object Slot OR null
	 * @access public
	 * @since 7/25/07
	 */
	function &getSlotForShortname ( $shortname ) {
		$query =& new SelectQuery;
		$query->addTable('segue_slot');
		$query->addTable('segue_slot_owner', LEFT_JOIN, 'segue_slot.shortname = segue_slot_owner.shortname');
		$query->addColumn('segue_slot.shortname', 'shortname');
		$query->addColumn('site_id');
		$query->addColumn('owner_id');
		$query->addWhereEqual('segue_slot.shortname', $shortname);
		
		$dbc =& Services::getService('DBHandler');
		$result =& $dbc->query($query, IMPORTER_CONNECTION);
		if ($result->getNumberOfRows()) {
			if ($result->field('shortname') !== '') {
				$slot = new Slot ($result->field('shortname'));
				
				// Add site ids from DB if it exists
				if ($result->field('site_id') !== '')
					$slot->populateSiteId($result->field('site_id'));
				
				
				while($result->hasMoreRows()) {
					if ($result->field('owner_id') !== '')
						$slot->populateOwnerId($result->field('owner_id'));
					$result->advanceRow();
				}
				
				return $slot;
			}
		}
		
		$null = null;
		return $null;
	}
	
	/**
	 * Answer true if the shortname is in use, false if it available.
	 * 
	 * @param string $shortname
	 * @return boolean
	 * @access public
	 * @since 7/25/07
	 */
	function isShortNameAvailable ( $shortname ) {
		return (!is_null($this->getSlotForShortname($shortname)));
	}
	
	/**
	 * Answer true if the shortname matches a 
	 * 
	 * @param string $shortname
	 * @param string $siteType
	 * @return boolean
	 * @access public
	 * @since 7/25/07
	 */
	function isShortnameValid ( $shortname, $siteType ) {
		return (!is_null($this->getSiteIdForShortname($shortname)));
	}
	
/*********************************************************
 * Owner Methods:
 * These allow for the setting and retreval of the owner
 * id of a site.
 *********************************************************/
	
	/**
	 * Answer site id strings that are owned by the current user
	 * 
	 * @return object Iterator
	 * @access public
	 * @since 7/26/07
	 */
	function getSlotsOwnedByUser () {
		$siteIds = array();
		$authNManager =& Services::getService('AuthN');
		$authTypes =& $authNManager->getAuthenticationTypes();
		while ($authTypes->hasNext()) {
			$authType =& $authTypes->next();
			if ($authNManager->isUserAuthenticated($authType)) {
				$siteIds = array_unique(array_merge($siteIds, 
					$this->getSlotsOwnedByAgent($authNManager->getUserId($authType))));
			}
		}
		
		return $siteIds;
	}
	
	/**
	 * Answer site id strings that are owned by the current user
	 * 
	 * @param object Id $agentId
	 * @return object Iterator
	 * @access public
	 * @since 7/26/07
	 */
	function getSlotsOwnedByAgent ( &$agentId ) {
		$siteIds = array();
		
		// Class slots
		$siteIds = array_merge($siteIds, $this->_getClassSlotsOwnedByAgent($agentId));
		
		// Personal slots
		$siteIds = array_merge($siteIds, $this->_getPersonalSlotsOwnedByAgent($agentId));
		
		// Custom-added slots
		$siteIds = array_merge($siteIds, $this->_getCustomSlotsOwnedByAgent($agentId));
		
		return $siteIds;
	}
	
	/**
	 * Answer the site id strings that are owned by the current user, that 
	 * are not auto-generated.
	 * 
	 * @param object Id $agentId
	 * @return object Iterator
	 * @access public
	 * @since 7/26/07
	 */
	function _getCustomSlotsOwnedByAgent ( &$agentId ) {
		$query =& new SelectQuery;
		$query->addTable('segue_site_owner');
		$query->addColumn('site_id');
		$query->addWhereEqual('owner_id', $agentId->getIdString());
		
		$dbc =& Services::getService('DBHandler');
		$result =& $dbc->query($query, IMPORTER_CONNECTION);
		$siteIds = array();
		while ($result->hasNext()) {
			$row = $result->next();
			$siteIds[] = $row['site_id'];
		}
		
		return $siteIds;
	}
}

?>