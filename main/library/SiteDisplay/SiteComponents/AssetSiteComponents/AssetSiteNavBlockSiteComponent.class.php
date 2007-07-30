<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteNavBlockSiteComponent.class.php,v 1.3 2007/07/30 17:07:46 adamfranco Exp $
 */ 

/**
 * The XML site nav block component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteNavBlockSiteComponent.class.php,v 1.3 2007/07/30 17:07:46 adamfranco Exp $
 */
class AssetSiteNavBlockSiteComponent
	extends AssetNavBlockSiteComponent
	// implements SiteNavBlockSiteComponent
{	

	/**
	 * Answers nothing because this is a top level nav
	 * 
	 * @return void
	 * @access public
	 * @since 4/12/06
	 */
	function getTargetId () {
		// don't ask me for this... my destination is hard-coded.
		throwError( new Error("SiteNavBlocks do not have self-defined target_id's", "SiteComponents"));
	}

	/**
	 * Accepts a visitor.
	 * 
	 * @param object Visitor
	 * @return object Component
	 * @access public
	 * @since 4/3/06
	 */
	function &acceptVisitor ( &$visitor ) {
		return $visitor->visitSiteNavBlock($this);
	}
	
	/**
	 * Answer an array (keyed by Id) of the possible destinations [organizers] that
	 * this component could be placed in.
	 * 
	 * @return ref array
	 * @access public
	 * @since 4/11/06
	 */
	function &getVisibleDestinationsForPossibleAddition () {
		$results = array();
		return $results;
	}
	
	/**
	 * Populate this object with default values
	 * 
	 * @return void
	 * @access public
	 * @since 7/26/07
	 */
	function populateWithDefaults () {
		parent::populateWithDefaults();
		
		$authNManager =& Services::getService('AuthN');
		$this->setOwnerId($authNManager->getFirstUserId());
	}
	
	/*********************************************************
	 * The following methods support working with site aliases.
	 * Aliases are syntactically-meaningful user-specified 
	 * identifiers for sites. Aliases are only guarenteed to be
	 * unique within the scope of a given segue installation.
	 *
	 * Only site nodes can have aliases.
	 *********************************************************/
	
	/**
	 * Answer the slot for a site id.
	 * 
	 * @return object Slot
	 * @access public
	 * @since 7/25/07
	 */
	function &getSlot () {
		$slotManager =& SlotManager::instance();
		return $slotManager->getSlotForSiteId($this->getId());
	}
}

?>