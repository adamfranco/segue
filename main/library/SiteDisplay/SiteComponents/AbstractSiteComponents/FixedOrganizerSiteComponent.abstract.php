<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: FixedOrganizerSiteComponent.abstract.php,v 1.2 2006/09/18 16:23:32 adamfranco Exp $
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
 * @version $Id: FixedOrganizerSiteComponent.abstract.php,v 1.2 2006/09/18 16:23:32 adamfranco Exp $
 */
class FixedOrganizerSiteComponent
	extends OrganizerSiteComponent
{	
	/**
	 * Add a subcomponent to an empty cell
	 * 
	 * @param object SiteComponent $siteComponent
	 * @param integer $cellIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function addSubcomponentToCell ( &$siteComponent, $cellIndex ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Swap the contents of two cells
	 * 
	 * @param integer $cellOneIndex
	 * @param integer $cellTwoIndex
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function swapCells ( $cellOneIndex, $cellTwoIndex ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the number of cells. Cells are indexed from zero
	 * 
	 * @return integer
	 * @access public
	 * @since 9/18/06
	 */
	function getNumCells () {
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
		return $visitor->visitFixedOrganizer($this);
	}
}

?>