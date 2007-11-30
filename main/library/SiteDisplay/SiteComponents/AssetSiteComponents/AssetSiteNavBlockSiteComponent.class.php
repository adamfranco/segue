<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteNavBlockSiteComponent.class.php,v 1.10 2007/11/30 20:23:19 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/../AbstractSiteComponents/SiteNavBlockSiteComponent.abstract.php");

/**
 * The XML site nav block component.
 * 
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AssetSiteNavBlockSiteComponent.class.php,v 1.10 2007/11/30 20:23:19 adamfranco Exp $
 */
class AssetSiteNavBlockSiteComponent
	extends AssetNavBlockSiteComponent
	implements SiteNavBlockSiteComponent
{	

	/**
	 * Answer the component class
	 * 
	 * @return string
	 * @access public
	 * @since 11/09/07
	 */
	function getComponentClass () {
		return 'SiteNavBlock';
	}
	
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
	public function acceptVisitor ( SiteVisitor $visitor, $inMenu = FALSE ) {
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
	function getVisibleDestinationsForPossibleAddition () {
		$results = array();
		return $results;
	}
	
	/**
	 * Answer the setting of 'showDisplayNames' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 11/30/07
	 */
	function showDisplayNames () {
		$showDisplayNames = parent::showDisplayNames();
		if ($showDisplayNames == 'default')
			return true;
		else
			return $showDisplayNames;
	}
	
	/**
	 * Answer the setting of 'commentsEnabled' for this component. 'default'
	 * indicates that a value set further up the hierarchy should be used.
	 * 
	 * @return mixed true, false, or 'default'
	 * @access public
	 * @since 11/30/07
	 */
	function commentsEnabled () {
		$commentsEnabled = parent::commentsEnabled();
		if ($commentsEnabled == 'default')
			return false;
		else
			return $commentsEnabled;
	}
	
	/*********************************************************
	 * The following methods support working with slots.
	 * Slots are syntactically-meaningful user-specified 
	 * identifiers for sites. Slots are only guarenteed to be
	 * unique within the scope of a given segue installation.
	 *
	 * Only site nodes can have slots.
	 *********************************************************/
	
	/**
	 * Answer the slot for a site id.
	 * 
	 * @return object Slot
	 * @access public
	 * @since 7/25/07
	 */
	function getSlot () {
		$slotManager = SlotManager::instance();
		return $slotManager->getSlotForSiteId($this->getId());
	}
}

?>