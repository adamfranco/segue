<?php
/**
 * @since 8/20/07
 * @package segue.coursemanager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueCourseSection.class.php,v 1.3 2008/01/04 18:43:20 adamfranco Exp $
 */ 

/**
 * This class represents a course in the Segue coursemanager system. This Course
 * is similar to the "CourseSection" in the CourseManagement OSID.
 * 
 * @since 8/20/07
 * @package segue.coursemanager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueCourseSection.class.php,v 1.3 2008/01/04 18:43:20 adamfranco Exp $
 */
class SegueCourseSection {

/*********************************************************
 * Static variables
 *********************************************************/

	/**
	 * @var array $instructorGroups;  
	 * @access public
	 * @static
	 * @since 8/16/07
	 */
	public static $instructorGroups = array();
	
	/**
	 * @var array $classGroupIdRegexp;  
	 * @access public
	 * @static
	 * @since 8/16/07
	 */
	public static $classGroupIdRegexp = '/^CN=.+,OU=[a-z0-9]+,OU=Classes,OU=Groups,DC=[a-z]+,DC=edu$/i';
	
	/**
	 * @var array $isInstructor; 
	 * @access private
	 * @static
	 * @since 8/16/07
	 */
	private static $isInstructor = array();
	
	
/*********************************************************
 * 	Instance variables
 *********************************************************/
	
	/**
	 * @var object Group $group;  
	 * @access protected
	 * @since 8/20/07
	 */
	protected $group;
	
	/**
	 * @var array $courseInfo;  
	 * @access private
	 * @since 8/21/07
	 */
	private $courseInfo;

/*********************************************************
 * Instance Methods
 *********************************************************/
 
 	/**
 	 * Constructor
 	 * 
 	 * @param object Group $group
 	 * @return void
 	 * @access public
 	 * @since 8/20/07
 	 */
 	public function __construct ( Group $group ) {
 		$this->group = $group;
 	}
		
	/**
	 * Answer the id for this course. This will likely correspond to some
	 * sort of course-section-code, but not necessarily
	 * 
	 * @return object Id
	 * @access public
	 * @since 8/20/07
	 */
	public function getId () {
		$idMgr = Services::getService("Id");
		return $idMgr->getId($this->group->getDisplayName());
	}
	
	/**
	 * Answer a display name string for the course. This may be its ID or something
	 * else.
	 * 
	 * @return string
	 * @access public
	 * @since 8/20/07
	 */
	public function getDisplayName () {
		return $this->group->getDisplayName();
	}
	
	/**
	 * Answer a description string for the course. This may be an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 8/20/07
	 */
	public function getDescription () {
		return $this->group->getDescription();
	}
	
	/**
	 * Answer true if the Agent Id passed is an instructor of this course
	 * 
	 * @param object Id $agentId
	 * @return boolean
	 * @access public
	 * @since 8/20/07
	 */
	public function isInstructor ( Id $agentId ) {
		// Since in this implementation "instructor" status is determined by 
		// membership in one of several groups designated as "Faculty" and hence
		// will be the same accross all courses in which the user is a member,
		// we are caching "instructor" status in a class variable.
		if (!isset(self::$isInstructor[$agentId->getIdString()])) {
// 			if ($agentId->getIdString() != '7362') {
// 				$agentManager = Services::getService("Agent");
// 				$agent = $agentManager->getAgentOrGroup($agentId);
// 				printpre("Checking if instructor. ".$agentId->getIdString()." (".$agent->getDisplayName().") not in:");
// 				printpre(self::$isInstructor);
// 				
// 				throw new Exception("testing");
// 			}
			// Match the groups the user is in against our configuration of
			// groups whose members should have personal sites.
			$agentManager = Services::getService("Agent");
			$ancestorSearchType = new HarmoniType("Agent & Group Search",
													"edu.middlebury.harmoni","AncestorGroups");
			$containingGroups = $agentManager->getGroupsBySearch(
							$agentId, $ancestorSearchType);
			$isInstructor = false;
			while (!$isInstructor && $containingGroups->hasNext()) {
				$group = $containingGroups->next();
				foreach (self::$instructorGroups as $validGroupId) {
					if ($validGroupId->isEqual($group->getId())) {
						$isInstructor = true;
						break;
					}
				}
			}
			
			self::$isInstructor[$agentId->getIdString()] = $isInstructor;
		}
		
		// Also verify that the user is a member in our group.		
		return (self::$isInstructor[$agentId->getIdString()] && 
				$this->group->contains($agentId, true));
	}
	
	/**
	 * Answer true if the current user is an instructor of this course
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/20/07
	 */
	public function isUserInstructor () {
		// This implementation will first get a list of all courses
		// a user is a member of, then check each one to see if the user
		// is the instructor of that course.
		$authN = Services::getService("AuthN");
		
		return $this->isInstructor($authN->getFirstUserId());
	}
	
	/**
	 * Answer true if the agent id passed is a student in the course
	 * 
	 * @param object Id $agentId
	 * @return boolean
	 * @access public
	 * @since 8/20/07
	 */
	public function isStudent ( Id $agentId ) {
		// Also verify that the user is a member in our group.		
		return ($this->group->contains($agentId, true)
			&& !self::$isInstructor[$agentId->getIdString()]);
	}
	
	/**
	 * Answer true if the current user is a student of this course
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/20/07
	 */
	public function isUserStudent () {
		// This implementation will first get a list of all courses
		// a user is a member of, then check each one to see if the user
		// is the instructor of that course.
		$authN = Services::getService("AuthN");
		
		return $this->isStudent($authN->getFirstUserId());
	}
	
	/**
	 * Answer an array of the Agent ids that are the instructors for the course.
	 * 
	 * @return array 
	 * @access public
	 * @since 8/20/07
	 */
	public function getInstructors () {
		$instructors = array();
		$members = $this->group->getMembers(false);
		
		while ($members->hasNext()) {
			$agentId = $members->next()->getId();
			if ($this->isInstructor($agentId)) {
				$instructors[] = $agentId;
			}
		}
		
		return $instructors;
	}
	
	/**
	 * Answer an array of the Agent ids that are the students for the course.
	 * 
	 * @return array 
	 * @access public
	 * @since 8/20/07
	 */
	public function getStudents () {
		$students = array();
		$members = $this->group->getMembers(false);
		
		while ($members->hasNext()) {
			$agentId = $members->next()->getId();
			if ($this->isStudent($agentId)) {
				$students[] = $agentId;
			}
		}
		
		return $students;
	}
	
	/**
	 * Answer the Id of the Group to be used when assigning authorizations to 
	 * all members of the course.
	 * 
	 * @return object Id
	 * @access public
	 * @since 8/20/07
	 */
	public function getGroupId () {
		return $this->group->getId();
	}
	
	/**
	 * Answer the department for the course.
	 * 
	 * @return string
	 * @access public
	 * @since 12/20/07
	 */
	public function getDepartment () {
		$info = $this->getInfo();
		if (!isset($info['department']))
			throw new Exception("No department available for this course section.");
		return $info['department'];
	}
	
	/**
	 * Answer the number for the course.
	 * 
	 * @return string
	 * @access public
	 * @since 12/20/07
	 */
	public function getNumber () {
		$info = $this->getInfo();
		if (!isset($info['number']))
			throw new Exception("No number available for this course section.");
		return $info['number'];
	}
	
	/**
	 * Answer the section for the course.
	 * 
	 * @return string
	 * @access public
	 * @since 12/20/07
	 */
	public function getSection () {
		$info = $this->getInfo();
		if (!isset($info['section']))
			throw new Exception("No section available for this course section.");
		return $info['section'];
	}
	
	/**
	 * Answer the year for the course.
	 * 
	 * @return integer
	 * @access public
	 * @since 8/21/07
	 */
	public function getYear () {
		$info = $this->getInfo();
		if (!isset($info['year']))
			throw new Exception("No year available for this course section.");
		return $info['year'];
	}
	
	/**
	 * Answer the semester for the course.
	 * 
	 * @return string
	 * @access public
	 * @since 8/21/07
	 */
	public function getSemester () {
		$info = $this->getInfo();
		if (!isset($info['semester']))
			throw new Exception("No semester available for this course section.");
		return $info['semester'];
	}
	
	/**
	 * Answer the semester order-key for the course, an integer
	 * 
	 * @return integer
	 * @access public
	 * @since 8/21/07
	 */
	public function getSemesterOrder () {
		$info = $this->getInfo();
		if (!isset($info['semester_order']))
			throw new Exception("No semester order available for this course section.");
		return $info['semester_order'];
	}
	
	/**
	 * Answer an approximate starting date for the semester
	 * 
	 * @return DateTime
	 * @access public
	 * @since 8/21/07
	 */
	public function getStartDate () {
		$info = $this->getInfo();
		
		if (!isset($info['semester_order']))
			throw new Exception("No start date available for this course section.");
		
		switch($info['semester_order']) {
			case 1:
				return DateAndTime::fromString($info['year']."-01-01T00:00:00");
			case 2:
				return DateAndTime::fromString($info['year']."-02-10T00:00:00");
			case 3:
				return DateAndTime::fromString($info['year']."-05-20T00:00:00");
			case 4:
				return DateAndTime::fromString($info['year']."-09-01T00:00:00");
		}
	}
	
	/**
	 * Answer an approximate ending date for the semester
	 * 
	 * @return DateAndTime
	 * @access public
	 * @since 8/21/07
	 */
	public function getEndDate () {
		$info = $this->getInfo();
		
		if (!isset($info['semester_order']))
			throw new Exception("No end date available for this course section.");
		
		switch($info['semester_order']) {
			case 1:
				return DateAndTime::fromString($info['year']."-02-10T00:00:00");
			case 2:
				return DateAndTime::fromString($info['year']."-05-20T00:00:00");
			case 3:
				return DateAndTime::fromString($info['year']."-09-01T00:00:00");
			case 4:
				return DateAndTime::fromString($info['year']."-12-25T00:00:00");
		}
	}
	
	/**
	 * Answer true if this class is in the future
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/21/07
	 */
	public function isFuture () {
		try {
			if ($this->getStartDate()->isGreaterThan(DateAndTime::now()))
				return true;
			else
				return false;
		} catch (Exception $e) {
			return true;
		}
	}
	
	/**
	 * Answer true if this class is in the current semester
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/21/07
	 */
	public function isCurrent () {
		try {
			if ($this->getStartDate()->isLessThan(DateAndTime::now()) && $this->getEndDate()->isGreaterThan(DateAndTime::now())) {
				return true;
			} else
				return false;
		} catch (Exception $e) {
			return false;
		}
	}
	
	/**
	 * Answer true if this class is in the past
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/21/07
	 */
	public function isPast () {
		try {
			if ($this->getEndDate()->isLessThan(DateAndTime::now()))
				return true;
			else
				return false;
		} catch (Exception $e) {
			return false;
		}
	}
	
	/**
	 * Parse the course code into its constituent parts
	 * 
	 * @return string
	 * @access private
	 * @since 8/21/07
	 */
	private function getInfo () {
		if (!is_array($this->courseInfo)) {
			$regex = '/^([a-z]+)([0-9]+)([a-z]+)-([a-z]+)([0-9]+)$/i';
			$semesterOnlyRegex = '/^.*-([a-z]+)([0-9]+)$/i';
			if (preg_match($regex, $this->getId()->getIdString(), $matches)) {
				$this->courseInfo = array(
					"department"	=> $matches[1],
					"number" 		=> $matches[2],
					"section"		=> $matches[3],
					);
				$semester = $matches[4];
				$year = $matches[5];
			} else if (preg_match($semesterOnlyRegex, $this->getId()->getIdString(), $matches)) 
			{
				$this->courseInfo = array();
				$semester = $matches[1];
				$year = $matches[2];
			} else {
				throw new Exception("Mal-formed or unknown-formed course code, '".$this->getId()->getIdString()."'.");
			}
			
			
			
			switch ($semester) {
				case 'w':
					$this->courseInfo['semester'] = _("Winter");
					$this->courseInfo['semester_order'] = "1";
					break;
				case 's':
					$this->courseInfo['semester'] = _("Spring");
					$this->courseInfo['semester_order'] = "2";
					break;
				case 'l':
					$this->courseInfo['semester'] = _("Language School");
					$this->courseInfo['semester_order'] = "3";
					break;
				case 'f':
					$this->courseInfo['semester'] = _("Fall");
					$this->courseInfo['semester_order'] = "4";
					break;
				default:
					throw new Exception("No semester found in course code, '".$this->getId()->getIdString()."'.");
			}
			
			$this->courseInfo['year'] = intval($year);
			if ($this->courseInfo['year'] < 1000)
				$this->courseInfo['year'] = $this->courseInfo['year'] + 2000;
				
		}
		
		return $this->courseInfo;
	}
}

?>