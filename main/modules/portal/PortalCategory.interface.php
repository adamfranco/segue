<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PortalCategory.interface.php,v 1.1 2008/04/01 20:32:50 adamfranco Exp $
 */ 

/**
 * A Portal Category is a container for folders. Each category implementation can
 * determine what folders are contained by them statically or programatically.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PortalCategory.interface.php,v 1.1 2008/04/01 20:32:50 adamfranco Exp $
 */
interface PortalCategory {
		
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
	 * Answer a string Identifier for this category that is unique within this 
	 * category list.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getIdString ();
	
	/**
	 * Answer an array of the folders in this category
	 * 
	 * @return array of PortalFolder objects
	 * @access public
	 * @since 4/1/08
	 */
	public function getFolders ();
	
	/**
	 * Answer a folder in this category by Id or throw an UnknownIdException.
	 * 
	 * @param string $idString
	 * @return PortalFolder
	 * @access public
	 * @since 4/1/08
	 */
	public function getFolder ($idString);
	
}

?>