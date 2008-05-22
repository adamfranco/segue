<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PortalFolder.interface.php,v 1.1 2008/04/01 20:32:50 adamfranco Exp $
 */ 

/**
 * A PortalFolder is a container for sites and placeholders (slots). 
 * Each folder implementation can determine what sites and placeholders are contained
 * by it.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PortalFolder.interface.php,v 1.1 2008/04/01 20:32:50 adamfranco Exp $
 */
interface PortalFolder {
		
	/**
	 * Answer a display Name for this category
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDisplayName ();
	
	/**
	 * Answer a description of this category for display purposes
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDescription ();
	
	/**
	 * Answer a string Identifier for this folder that is unique within this folder's
	 * category.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getIdString ();
	
	/**
	 * Answer an array of the slots in this category.
	 * 
	 * @return array of Slot objects
	 * @access public
	 * @since 4/1/08
	 */
	public function getSlots ();
	
	/**
	 * Answer a string of controls html to go along with this folder. In many cases
	 * it will be empty, but some implementations may need controls for adding new slots.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getControlsHtml ();
	
	/**
	 * Answer true if the slots returned by getSlots() have already been filtered
	 * by authorization and authorization checks should only be done when printing.
	 * This method enables speed increases on long pre-sorted lists.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/21/08
	 */
	public function slotsAreFilteredByAuthorization ();
	
	/**
	 * Answer true if the edit controls should be displayed for the sites listed.
	 * If true, this can lead to slowdowns as authorizations are checked on large
	 * lists of large sites.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/21/08
	 */
	public function showEditControls ();
}

?>