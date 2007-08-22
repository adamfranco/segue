<?php
/**
 * @since 8/14/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CourseSlot.class.php,v 1.1 2007/08/22 20:08:51 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/Slot.abstract.php");

/**
 * A course slot appears by default for all instructors of a course.
 *
 * The current implementation makes use of groups rather than the coursemanagement
 * system due to the state of the current coursemanagement implementation. This 
 * implementation should be rewritten to make use of a coursemananagement OSID
 * implementation in the future.
 * 
 * @since 8/14/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CourseSlot.class.php,v 1.1 2007/08/22 20:08:51 adamfranco Exp $
 */
class CourseSlot
	extends Slot
{

	/**
	 * Answer the external slots for the current user
	 * 
	 * @return array
	 * @access protected
	 * @static
	 * @since 8/14/07
	 */
	public static function getExternalSlotDefinitionsForUser () {
		$courseMgr = SegueCourseManager::instance();
		$slots = array();
		
		$courses = $courseMgr->getUsersInstructorCourses();
		foreach ($courses as $course) {
			$slot = new CourseSlot($course->getId()->getIdString());
			foreach ($course->getInstructors() as $instructor) {
				$slot->populateOwnerId($instructor);
			}
			$slots[] = $slot;
		}
		
		return $slots;
	}

/*********************************************************
 * Instance Methods
 *********************************************************/

	/**
	 * Answer the type of slot for this instance
	 * 
	 * @return string
	 * @access public
	 * @since 8/14/07
	 */
	public function getType () {
		return "course";
	}

	/**
	 * Given an internal definition of the slot, load any extra owners
	 * that might be in an external data source.
	 * 
	 * @return void
	 * @access public
	 * @since 8/14/07
	 */
	public function mergeWithExternal () {
		$courseMgr = SegueCourseManager::instance();
		$idMgr = Services::getService("Id");
		$course = $courseMgr->getCourse($idMgr->getId($this->getShortname()));
		
		foreach ($course->getInstructors() as $instructor) {
			if (!$this->isOwner($instructor) && !$this->isRemovedOwner($instructor)) {
				$this->populateOwnerId($instructor);
			}
		}
	}
}

?>