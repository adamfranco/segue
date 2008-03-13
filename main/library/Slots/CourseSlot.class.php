<?php
/**
 * @since 8/14/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CourseSlot.class.php,v 1.4 2008/03/13 13:29:29 adamfranco Exp $
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
 * @version $Id: CourseSlot.class.php,v 1.4 2008/03/13 13:29:29 adamfranco Exp $
 */
class CourseSlot
	extends SlotAbstract
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
 			
// 			// This is the old method, loading all instructors right away.
// 			// This method is simpler and should be used if the underlying
// 			// course information system is more efficient than the
// 			// one in place at the time of this writing.
// 			foreach ($course->getInstructors() as $instructor) {
// 				$slot->populateOwnerId($instructor);
// 			}
			
			// Attach the course object so that we can lazily fetch the owner list
			// as needed rather than looking to see if every person in the class is an
			// instructor now.
			$slot->attachCourse($course);
			
			$slots[] = $slot;
		}
		
		return $slots;
	}
	

/*********************************************************
 * Instance Methods
 *********************************************************/

	/**
	 * Answer the type of slot for this instance. The type of slot corresponds to
	 * how it is populated/originated. Some slots are originated programatically,
	 * others are added manually. The type should not be used for classifying where
	 * as site should be displayed. Use the location category for that.
	 * 
	 * @return string
	 * @access public
	 * @since 8/14/07
	 */
	public function getType () {
		return "course";
	}
	
	/**
	 * Answer the default category for the slot.
	 * 
	 * @return string
	 * @access public
	 * @since 12/6/07
	 */
	public function getDefaultLocationCategory () {
		return 'main';
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
	
/*********************************************************
 * The following instance variables and over-ridden methods
 * allow the CourseSlot to take a more efficient approach to
 * determining if the current user is the owner of a course.
 *
 * The original method involved determining the 'instructor' or
 * 'student' status of all members of the course, when in many
 * cases, only the status of the current user is of interest.
 *********************************************************/
	
	/**
	 * @var SegueCourseSection $course;  
	 * @access private
	 * @since 1/3/08
	 */
	private $course;
	
	/**
	 * @var boolean $ownersPopulated;  
	 * @access private
	 * @since 1/3/08
	 */
	private $ownersPopulated = false;
	
	/**
	 * Attach a course to this slot.
	 * 
	 * @param object SegueCourseSection $courseSection
	 * @return void
	 * @access private
	 * @since 1/3/08
	 */
	private function attachCourse (SegueCourseSection $courseSection) {
		$this->course = $courseSection;
	}
	
	/**
	 * Answer true if the current user is an owner.
	 * This method has been overridden to provide a quicker method of determination
	 * when the slot is created with a course object.
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/22/07
	 */
	public function isUserOwner () {
		if (isset($this->course) && !is_null($this->course)) {
			// False if the user has been manually removed as an owner.
			if ($this->isUserRemovedOwner())
				return false;
			
			// If they weren't manually removed and are listed as an instructor,
			// then they are an owner.
			if ($this->course->isUserInstructor())
				return true;
			
			// Go through the internally defined owners and check them.
			// This is the same process the parent uses, we just want to avoid
			// loading checking all of the course members, since we already know
			// the result of that process for the current user (just above).
			$authN = Services::getService("AuthN");
			$userId = $authN->getFirstUserId();
			foreach (parent::getOwners() as $id) {
				if ($id->isEqual($userId))
					return true;
			}		
			return false;	
		}
		
		return parent::isUserOwner();
	}
	
	/**
	 * Answer the Id objects of the owners of this slot.
	 * This method has been over-ridden to allow lazy loading of slot owners from
	 * a course object rather than forcing that to be done at instance creation time.
	 * 
	 * @return array
	 * @access public
	 * @since 7/30/07
	 */
	public function getOwners () {
		// Lazily load the slot owners.
		if (!$this->ownersPopulated && isset($this->course)) {
			
			foreach ($this->course->getInstructors() as $instructor) {
				$this->populateOwnerId($instructor);
			}
			
			$this->ownersPopulated = true;
		}
		return parent::getOwners();
	}
}

?>