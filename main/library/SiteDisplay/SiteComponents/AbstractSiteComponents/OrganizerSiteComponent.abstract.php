<?php
/**
 * @since 3/30/06
 * @package segue.libraries.site_display
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: OrganizerSiteComponent.abstract.php,v 1.3 2007/08/31 16:03:45 achapin Exp $
 */ 

require_once(dirname(__FILE__)."/OrganizerSiteComponent.abstract.php");


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
 * @version $Id: OrganizerSiteComponent.abstract.php,v 1.3 2007/08/31 16:03:45 achapin Exp $
 */
interface OrganizerSiteComponent
	extends SiteComponent
{
		
	/**
	 * Answer the integer number of rows
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	public function getNumRows () ;
	
	/**
	 * Update the number of rows. The contents of this organizer may limit the
	 * ability to reduce the number of rows.
	 * 
	 * @param integer $newRows
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function updateNumRows ( $newRows ) ;
	
	/**
	 * Answer the integer number of columns
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	public function getNumColumns () ;
	
	/**
	 * Update the number of columns. The contents of this organizer may limit the
	 * ability to reduce the number of columns.
	 * 
	 * @param integer $newColumns
	 * @return void
	 * @access public
	 * @since 3/31/06
	 */
	public function updateNumColumns ( $newColumns ) ;
	
	/**
	 * Answer the total number of cells in this organizer. (Some may be empty)
	 * 
	 * @return integer
	 * @access public
	 * @since 3/31/06
	 */
	public function getTotalNumberOfCells () ;
	
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
	public function getDirection () ;
	
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
	public function updateDirection ( $direction ) ;
	
	/**
	 * Answer the subcomponent that is in a cell, null if empty
	 * 
	 * @param integer $cellIndex
	 * @return mixed object or null
	 * @access public
	 * @since 3/31/06
	 */
	public function getSubcomponentForCell ( $cellIndex ) ;
	
	/**
	 * Answer the NavOrganizer above this organizer.
	 * 
	 * @return object NavOrganizerSiteComponent
	 * @access public
	 * @since 4/11/06
	 */
	function getParentNavOrganizer () ;
	
}

?>