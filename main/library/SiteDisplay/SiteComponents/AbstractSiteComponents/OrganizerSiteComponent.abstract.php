<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: OrganizerSiteComponent.abstract.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
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
 * @version $Id: OrganizerSiteComponent.abstract.php,v 1.1 2006/04/05 16:11:30 adamfranco Exp $
 */
class OrganizerSiteComponent
	extends SiteComponent
{
		
	/**
	 * Answer the integer number of rows
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getNumRows () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Update the number of rows. The contents of this organizer may limit the
	 * ability to reduce the number of rows.
	 * 
	 * @param integer $newRows
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateNumRows ( $newRows ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the integer number of columns
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getNumColumns () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Update the number of columns. The contents of this organizer may limit the
	 * ability to reduce the number of columns.
	 * 
	 * @param integer $newColumns
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	function updateNumColumns ( $newColumns ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the total number of cells in this organizer. (Some may be empty)
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getTotalNumberOfCells () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the number of cells in this organizer that are visible (some may
	 * be empty).
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	function getNumberOfVisibleCells () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the direction of indexing:
	 * 		Left-Right/Top-Bottom
	 *		Top-Bottom/Left-Right
	 *		Right-Left/Top-Bottom
	 *		Top-Bottom/Right-Left
	 * 		Left-Right/Bottom-Top
	 *		Bottom-Top/Left-Right
	 *		Right-Left/Bottom-Top
	 *		Bottom-Top/Right-Left
	 * 
	 * @return string
	 * @access public
	 * @since 3/31/06
	 */
	function getDirection () {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Update the direction of indexing:
	 * 		Left-Right/Top-Bottom
	 *		Top-Bottom/Left-Right
	 *		Right-Left/Top-Bottom
	 *		Top-Bottom/Right-Left
	 * 		Left-Right/Bottom-Top
	 *		Bottom-Top/Left-Right
	 *		Right-Left/Bottom-Top
	 *		Bottom-Top/Right-Left
	 * 
	 * @param string $direction
	 * @access public
	 * @since 3/31/06
	 */
	function updateDirection ( $direction ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
	/**
	 * Answer the subcomponent that is in a cell, null if empty
	 * 
	 * @param integer $cellIndex
	 * @return mixed object or null
	 * @access public
	 * @since 3/31/06
	 */
	function &getSubcomponentForCell ( $cellIndex ) {
		throwError(new Error("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.", "SiteDisplay")); 
	}
	
}

?>