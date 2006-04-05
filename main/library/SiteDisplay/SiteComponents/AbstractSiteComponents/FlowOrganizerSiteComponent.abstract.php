<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: FlowOrganizerSiteComponent.abstract.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
 */ 

/**
 * The Organizer subdivides its bounding cell and arranges its subcomponents in
 * those subdivisions
 * 
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: FlowOrganizerSiteComponent.abstract.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
 */
class FlowOrganizerSiteComponent
	extends OrganizerSiteComponent
{

	/**
	 * Answer the maximum number of cells that can be displayed before overflowing
	 * (i.e. to pagination, archiving, hiding, etc).
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getMaxVisible () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Update the maximum number of cells that can be displayed before overflowing
	 * (i.e. to pagination, archiving, hiding, etc).
	 * 
	 * @param integer $newMaxVisible Greater than or equal to 1
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateMaxVisible ( $newMaxVisible ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Get the overflow style:
	 *		Paginate
	 *		Archive
	 *		Hide
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getOverflowStyle () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Update the overflow style:
	 *		Paginate
	 *		Archive
	 *		Hide
	 * 
	 * @param string $overflowStyle
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateOverflowStyle () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Add a subcomponent
	 * 
	 * @param object SiteComponent $siteComponent
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function addSubcomponent ( &$siteComponent ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Move the contents of cellOneIndex before cellTwoIndex
	 * 
	 * @param integer $cellOneIndex
	 * @param integer $cellTwoIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function moveBefore ( $cellOneIndex, $cellTwoIndex ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Move the contents of cellIndex to the end of the organizer
	 * 
	 * @param integer $cellIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function moveToEnd ( $cellIndex ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
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
		return $visitor->visitFlowOrganizer($this);
	}

}

?>