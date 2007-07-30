<?php
/**
 * @since 7/27/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Slot.class.php,v 1.1 2007/07/30 18:13:40 adamfranco Exp $
 */ 

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
 * @version $Id: Slot.class.php,v 1.1 2007/07/30 18:13:40 adamfranco Exp $
 */
class Slot {

	/**
	 * @var string $_shortname;  
	 * @access private
	 * @since 7/30/07
	 */
	var $_shortname;
	
	/**
	 * @var mixed $_siteId;  
	 * @access private
	 * @since 7/30/07
	 */
	var $_siteId = null;
	
	/**
	 * @var array $_owners;  
	 * @access private
	 * @since 7/30/07
	 */
	var $_owners = array();
		
	/**
	 * Constructor
	 * 
	 * @param string $shortname
	 * @return void
	 * @access public
	 * @since 7/30/07
	 */
	function Slot ( $shortname ) {
		$this->_shortname = $shortname;
	}
	
	/**
	 * Answer the shortname of this slot
	 * 
	 * @return string
	 * @access public
	 * @since 7/30/07
	 */
	function getShortname () {
		return $this->_shortname;
	}
	
	/**
	 * Answer true if this slot has an existing site.
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/30/07
	 */
	function siteExists () {
		if (is_null($this->_siteId)) 
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
	function &getSiteId () {
		if (!is_null($this->_siteId)) {
			return $this->_siteId;		
		}
		
		$null = null;
		return null;
	}
	
	/**
	 * Answer the owners of this slot
	 * 
	 * @return array
	 * @access public
	 * @since 7/30/07
	 */
	function getOwners () {
		return $this->_owners;
	}
	
	/**
	 * Store a siteId in this object, does not update the database. 
	 * This method is internal to this package and should not be used
	 * by clients.
	 * 
	 * @param mixed siteId string or Id object
	 * @return void
	 * @access protected
	 * @since 7/30/07
	 */
	function populateSiteId ( $siteId ) {
		if (is_null($siteId))
			return;
			
		if (is_object($siteId))
			$this->_siteId = $siteId;
		else {
			$idManager =& Services::getService("Id");
			$this->_siteId = $idManager->getId($siteId);
		}
	}
	
	/**
	 * Add a site owner in this object, does not update the database. 
	 * This method is internal to this package and should not be used
	 * by clients.
	 * 
	 * @param mixed siteId string or Id object
	 * @return void
	 * @access protected
	 * @since 7/30/07
	 */
	function populateOwnerId ( $ownerId ) {
		if (is_null($ownerId))
			return;
			
		if (is_object($ownerId))
			$this->_owners[] = $ownerId;
		else {
			$idManager =& Services::getService("Id");
			$this->_owners[] = $idManager->getId($ownerId);
		}
	}
}

?>