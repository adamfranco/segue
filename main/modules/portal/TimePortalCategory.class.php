<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AccessPortalCategory.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/MainPortalCategory.class.php");

require_once(dirname(__FILE__)."/RecentlyVisitedPortalFolder.class.php");


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
 * @version $Id: AccessPortalCategory.class.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */
class TimePortalCategory
	extends MainPortalCategory 
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
		$this->folders[] = new RecentlyVisitedPortalFolder;
	}
		
	/**
	 * Answer a display Name for this category
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDisplayName () {
		return _("Recent Activity");
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
		return 'time';
	}
	
}

?>