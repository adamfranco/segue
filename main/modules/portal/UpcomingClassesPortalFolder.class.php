<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: UpcomingClassesPortalFolder.class.php,v 1.1 2008/04/01 20:32:50 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/ClassesPortalFolder.abstract.php");

/**
 * The PersonalPortalFolder contains all personal sites.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: UpcomingClassesPortalFolder.class.php,v 1.1 2008/04/01 20:32:50 adamfranco Exp $
 */
class UpcomingClassesPortalFolder
	extends ClassesPortalFolder
	implements PortalFolder 
{
		
	/**
	 * Answer a display Name for this category
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getDisplayName () {
		return _("Upcoming Classes");
	}
	
	/**
	 * Answer a string Identifier for this folder that is unique within this folder's
	 * category.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getIdString () {
		return "upcoming_classes";
	}
	
	/**
	 * Answer an array of courses for this folder
	 * 
	 * @return array of course objects
	 * @access protected
	 * @since 4/1/08
	 */
	protected function getCourses () {
		$courseMgr = SegueCourseManager::instance();
		return $courseMgr->getUsersFutureCourses(SORT_DESC);
	}
}