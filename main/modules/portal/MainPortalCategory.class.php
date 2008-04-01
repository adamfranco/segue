<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: MainPortalCategory.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/PortalCategory.interface.php");

require_once(dirname(__FILE__)."/PersonalPortalFolder.class.php");
require_once(dirname(__FILE__)."/OtherOwnedPortalFolder.class.php");


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
 * @version $Id: MainPortalCategory.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */
class MainPortalCategory
	implements PortalCategory 
{

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 4/1/08
	 */
	public function __construct () {
		$this->folders = array();
		$this->folders[] = new PersonalPortalFolder;
		$this->folders[] = new OtherOwnedPortalFolder;
	}
		
	/**
	 * Answer a display Name for this category
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDisplayName () {
		return _("Owned By You");
	}
	
	/**
	 * Answer a description of this category for display purposes
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDescription () {
		return "";
	}
	
	/**
	 * Answer a string Identifier for this category that is unique within this 
	 * category list.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getIdString () {
		return 'main';
	}
	
	/**
	 * Answer an array of the folders in this category
	 * 
	 * @return array of PortalFolder objects
	 * @access public
	 * @since 4/1/08
	 */
	public function getFolders () {
		return $this->folders;
	}
	
	/**
	 * Answer a folder in this category by Id or throw an UnknownIdException.
	 * 
	 * @param string $idString
	 * @return PortalFolder
	 * @access public
	 * @since 4/1/08
	 */
	public function getFolder ($idString) {
		foreach ($this->folders as $folder) {
			if ($folder->getIdString() == $idString)
				return $folder;
		}
		
		throw new UnknownIdException("No folder with id, '$idString', exists here.");
	}
	
}

?>