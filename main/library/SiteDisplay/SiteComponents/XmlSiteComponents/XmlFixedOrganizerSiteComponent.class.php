<?php
/**
 * @since 4/3/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlFixedOrganizerSiteComponent.class.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
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
 * @version $Id: XmlFixedOrganizerSiteComponent.class.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
 */
class XmlFixedOrganizerSiteComponent
	extends XmlOrganizerSiteComponent 
	// implements FixedOrganizerSiteComponent
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
	 * Answer the ordered indices.
	 * 
	 * Currently Ignoring Direction and assuming left-right/top-bottom
	 * @return array
	 * @access public
	 * @since 4/3/06
	 */
	function getVisibleOrderedIndices () {
		$rows = $this->getNumRows();
		$cols = $this->getNumColumns();
		$array = array();
		for ($i = 0; $i < $rows*$cols; $i++) {
			$array[] = $i;
		}
		return $array;
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