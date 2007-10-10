<?php
/**
 * @since 8/20/07
 * @package segue.coursemanager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueCourseManager.class.php,v 1.4 2007/10/10 22:58:46 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueCourseSection.class.php");
require_once(dirname(__FILE__)."/SegueCourseGroup.class.php");

/**
 * This Course Manager is a very simplified system used by Segue 2 to fetch the 
 * membership of a course. Depending on the complexity of the CourseManagement system
 * in OSID v3, this system could be replaced with an OSID v3 CourseManagement 
 * implementation. If that is not done, the implementation should eventually be
 * re-worked to at least use the OSID CourseManagement as a data source ranther 
 * than the initially built group-based structure.
 * 
 * @since 8/20/07
 * @package segue.coursemanager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueCourseManager.class.php,v 1.4 2007/10/10 22:58:46 adamfranco Exp $
 */
class SegueCourseManager {

/*********************************************************
 * Static Variables
 *********************************************************/

	/**
	 * @var array $classGroupIdRegexp;  
	 * @access public
	 * @static
	 * @since 8/16/07
	 */
	public static $classGroupIdRegexp = '/^CN=.+,OU=[a-z0-9]+,OU=Classes,OU=Groups,DC=[a-z]+,DC=edu$/i';

/*********************************************************
 * Instance Creation
 *********************************************************/

	
	/**
 	 * @var object  $instance;  
 	 * @access private
 	 * @since 10/10/07
 	 * @static
 	 */
 	private static $instance;

	/**
	 * This class implements the Singleton pattern. There is only ever
	 * one instance of the this class and it is accessed only via the 
	 * ClassName::instance() method.
	 * 
	 * @return object 
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	public static function instance () {
		if (!isset(self::$instance))
			self::$instance = new SegueCourseManager;
		
		return self::$instance;
	}
	
	/**
	 * Constructor, private to make sure that no one build this object like this:
	 * <code>$courseManager = new SegueCourseManager()</code>
	 *
	 * @return void
	 * @access private
	 * @since 8/14/07
	 */
	private function __construct() {}
	
	
/*********************************************************
 * Instance Methods
 *********************************************************/
	
	/**
	 * Answer a list of courses of which an Agent is an instructor.
	 * 
	 * @param object Id $agentId
	 * @return array
	 * @access public
	 * @since 8/20/07
	 */
	public function getInstructorCourses ( Id $agentId, $sortDirection = SORT_ASC ) {
		$courses = array();
		foreach ($this->getAllCourses($agentId, $sortDirection) as $course) {
			if ($course->isInstructor($agentId))
				$courses[] = $course;
		}
		return $courses;
	}
	
	/**
	 * Answer a list of courses of which an Agent is a student.
	 * 
	 * @param object Id $agentId
	 * @return array
	 * @access public
	 * @since 8/20/07
	 */
	public function getStudentCourses ( Id $agentId, $sortDirection = SORT_ASC ) {
		$courses = array();
		foreach ($this->getAllCourses($agentId, $sortDirection) as $course) {
			if ($course->isStudent($agentId))
				$courses[] = $course;
		}
		return $courses;
	}
	
	/**
	 * Answer all of the courses in which the agent is a student or instructor
	 * 
	 * @param object Id $agentId
	 * @return array
	 * @access public
	 * @since 8/20/07
	 */
	public function getAllCourses ( Id $agentId, $sortDirection = SORT_ASC ) {
		$agentManager = Services::getService("Agent");
		$ancestorSearchType = new HarmoniType("Agent & Group Search",
												"edu.middlebury.harmoni","AncestorGroups");
		$containingGroups = $agentManager->getGroupsBySearch(
						$agentId, $ancestorSearchType);
						
		$courseSections = array();
		while ($containingGroups->hasNext()) {
			$group = $containingGroups->next();
			if (preg_match(self::$classGroupIdRegexp, $group->getId()->getIdString())) {
				$courseSections[] = $this->getCourseForGroup($group);
			}
		}
		
		return $this->sortCourses($courseSections, $sortDirection);
	}
	
	/**
	 * Answer a list of courses of which the current user is an instructor.
	 * 
	 * @return array
	 * @access public
	 * @since 8/20/07
	 */
	public function getUsersInstructorCourses ( $sortDirection = SORT_ASC ) {
		// This implementation will first get a list of all courses
		// a user is a member of, then check each one to see if the user
		// is the instructor of that course.
		$authN = Services::getService("AuthN");
		
		return $this->getInstructorCourses($authN->getFirstUserId(), $sortDirection);
	}
	
	/**
	 * Answer a list of courses of which the current user is an student.
	 * 
	 * @return array
	 * @access public
	 * @since 8/20/07
	 */
	public function getUsersStudentCourses ( $sortDirection = SORT_ASC ) {
		// This implementation will first get a list of all courses
		// a user is a member of, then check each one to see if the user
		// is the instructor of that course.
		$authN = Services::getService("AuthN");
		
		return $this->getStudentCourses($authN->getFirstUserId(), $sortDirection);
	}
	
	/**
	 * Answer a list of all courses of which the current user is a member.
	 * 
	 * @return array
	 * @access public
	 * @since 8/20/07
	 */
	public function getUsersCourses ($sortDirection = SORT_ASC) {
		// This implementation will first get a list of all courses
		// a user is a member of, then check each one to see if the user
		// is the instructor of that course.
		$authN = Services::getService("AuthN");
		
		return $this->getAllCourses($authN->getFirstUserId(), $sortDirection);
	}
	
	/**
	 * Answer the future courses for the user
	 * 
	 * @param optional $sortDirection SORT_ASC or SORT_DESC
	 * @return array
	 * @access public
	 * @since 8/22/07
	 */
	public function getUsersFutureCourses ($sortDirection = SORT_ASC) {
		$courses = array();
		foreach ($this->getUsersCourses($sortDirection) as $course) {
			if ($course->isFuture()) {
				$courses[] = $course;
			}
		}
		
		return $courses;
	}
	
	/**
	 * Answer the current courses for the user
	 * 
	 * @param optional $sortDirection SORT_ASC or SORT_DESC
	 * @return array
	 * @access public
	 * @since 8/22/07
	 */
	public function getUsersCurrentCourses ($sortDirection = SORT_ASC) {
		$courses = array();
		foreach ($this->getUsersCourses($sortDirection) as $course) {
			if ($course->isCurrent()) {
				$courses[] = $course;
			}
		}
		
		return $courses;
	}
	
	/**
	 * Answer the past courses for the user
	 * 
	 * @param optional $sortDirection SORT_ASC or SORT_DESC
	 * @return array
	 * @access public
	 * @since 8/22/07
	 */
	public function getUsersPastCourses ($sortDirection = SORT_ASC) {
		$courses = array();
		foreach ($this->getUsersCourses($sortDirection) as $course) {
			if ($course->isPast()) {
				$courses[] = $course;
			}
		}
		
		return $courses;
	}
	
	/**
	 * Sort an array of courses chronologically
	 * 
	 * @param array $courses
	 * @param optional $sortDirection SORT_ASC or SORT_DESC
	 * @return array
	 * @access private
	 * @since 8/22/07
	 */
	private function sortCourses (array $courses, $sortDirection) {
		if ($sortDirection != SORT_ASC && $sortDirection != SORT_DESC)
			throw new Exception("Unknown sort direction, '".$sortDirection."', should be one of the constants SORT_ASC or SORT_DESC.");
		
		// Load up our Sorting arrays
		$years = array();
		$semesterOrder = array();
		$ids = array();
		foreach ($courses as $course) {
			$years[] = $course->getYear();
			$semesterOrder[] = $course->getSemesterOrder();
			$ids[] = $course->getId()->getIdString();
		}
		
		array_multisort(
			$years, SORT_NUMERIC, $sortDirection,
			$semesterOrder, SORT_NUMERIC, $sortDirection,
			$ids, SORT_STRING, SORT_ASC,
			$courses
			);
		
		return $courses;
	}
	
	/**
	 * Answer a course by Id
	 * 
	 * @param object Id $id
	 * @return object SegueCourseSection
	 * @access public
	 * @since 8/20/07
	 */
	public function getCourse ( Id $id ) {
		// Check to see if there is a CourseGroup with this Id
		try {
			$this->getCourseForGroupId($id);
		
		} catch (Exception $e) {
			// If we didn't configure LDAP Authentication, we're not going to be able to use this
			if (!class_exists('ClassTokenSearch'))
				throw new Exception("LDAP Authentication is not configured.");
			
			// If we didn't have a CourseGroup or a loaded Course, look up the groupId
			// Fetch the course group that matches the id passed.
			$searchType = new ClassTokenSearch();
			$string = "*".$id->getIdString()."*";
			$dns = $searchType->getClassDNsBySearch($string);
			
			if (count($dns)) {
				$idMgr = Services::getService("Id");
				return $this->getCourseForGroupId($idMgr->getId($dns[0]));
			} else {
				throw new Exception("No course exists with id, '".$id->getIdString()."'.");
			}
		}
	}
	
	/**
	 * Answer a course from a groupId
	 * 
	 * @param object Id $groupId
	 * @return object SegueCourseSection
	 * @access private
	 * @since 8/20/07
	 */
	private function getCourseForGroupId ( Id $groupId ) {
		if (!isset($this->courseSections[$groupId->getIdString()])) {
			$agentManager = Services::getService("Agent");
			if (!$agentManager->isGroup($groupId))
				throw new Exception("No Group exists with id, '".$groupId->getIdString()."'.");
			
			$group = $agentManager->getGroup($groupId);
			return $this->getCourseForGroup($group);
			
		}
		return $this->courseSections[$groupId->getIdString()];
	}
	
	/**
	 * Answer a course from a group object
	 * 
	 * @param object Group $group
	 * @return object CourseSection
	 * @access private
	 * @since 8/20/07
	 */
	private function getCourseForGroup ( Group $group ) {
		$groupId = $group->getId();
		if (!isset($this->courseSections[$groupId->getIdString()])) {			
			if ($group->getType()->isEqual(new Type ("segue", "edu.middlebury", "coursegroup"))) {
				$this->courseSections[$groupId->getIdString()] = new SegueCourseGroup($group);
			} else {
				$this->courseSections[$groupId->getIdString()] = new SegueCourseSection($group);
			}
		}
		return $this->courseSections[$groupId->getIdString()];
	}
	
	/**
	 * Create a new CourseGroup
	 * 
	 * @param object Id $id The Identifier to use for this Course Group. This may
	 *						be related to some sort of course code if desired.
	 * @param string $displayName
	 * @return object SegueCourseGroup
	 * @access public
	 * @since 8/20/07
	 */
	public function createCourseGroup ( Id $id, $displayName ) {
		$agentMgr = Services::getService("Agent");
		$idMgr = Services::getService("Id");
		$rootGroup = $agentMgr->getGroup($idMgr->getId("edu.middlebury.segue.coursegroups"));
		
		$propType = new Type ("segue", "edu.middlebury", "coursegroup");
		$properties = new HarmoniProperties($propType);
		$idString = $id->getIdString();
		$properties->addProperty("CourseGroupId", $idString);
		
		$courseGroup = $agentMgr->createGroup(
			$displayName,
			new Type('segue', 'edu.middlebury', 'coursegroup'),
			"",
			$properties);
		
		$rootGroup->add($courseGroup);
		
		return $this->getCourseForGroup($courseGroup);
	}
}

?>