<?php
/**
 * @since 8/20/07
 * @package segue.coursemanager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueCourseGroup.class.php,v 1.1 2007/08/22 20:08:50 adamfranco Exp $
 */ 

/**
 * The SegueCourseGroup is a type of Course which is made up from multiple 
 * SegueCourseSections.
 * 
 * @since 8/20/07
 * @package segue.coursemanager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueCourseGroup.class.php,v 1.1 2007/08/22 20:08:50 adamfranco Exp $
 */
class SegueCourseGroup 
	extends SegueCourseSection
{

/*********************************************************
 * Instance Variables
 *********************************************************/

	/**
	 * @var array $sections;  
	 * @access private
	 * @since 8/20/07
	 */
	private $sections = array();
	
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
		
		$groups = $this->group->getGroups(false);
		while ($groups->hasNext()) {
			$this->sections[] = new SegueCourseSection($groups->next());
		}
	}
	
	/**
	 * Add a SegueCourseSection to this group
	 * 
	 * @param object SegueCourseSection $section
	 * @return void
	 * @access public
	 * @since 8/20/07
	 */
	public function addSection ( SegueCourseSection $section ) {
		$this->group->add($section->group);
		$this->sections[] = $section;
	}
	
	/**
	 * Answer the id for this course. This will likely correspond to some
	 * sort of course-section-code, but not necessarily.
	 * 
	 * @return object Id
	 * @access public
	 * @since 8/20/07
	 */
	public function getId () {
		$propType = new Type ("segue", "edu.middlebury", "coursegroup");
		$properties = $this->group->getPropertiesByType($propType);
		$idMgr = Services::getService("Id");
		return $idMgr->getId($properties->getProperty("CourseGroupId"));
	}
	
// 	/**
// 	 * Answer true if the Agent Id passed is an instructor of this course
// 	 * 
// 	 * @param object Id $agentId
// 	 * @return boolean
// 	 * @access public
// 	 * @since 8/20/07
// 	 */
// 	public function isInstructor ( Id $agentId ) {
// 		foreach ($this->sections as $section) {
// 			if ($section->isInstructor($agentId))
// 				return true;
// 		}
// 		
// 		return false;
// 	}
// 	
// 	/**
// 	 * Answer true if the agent id passed is a student in the course
// 	 * 
// 	 * @param object Id $agentId
// 	 * @return boolean
// 	 * @access public
// 	 * @since 8/20/07
// 	 */
// 	public function isStudent ( Id $agentId ) {
// 		foreach ($this->sections as $section) {
// 			if ($section->isStudent($agentId))
// 				return true;
// 		}
// 		
// 		return false;
// 	}
	
	/**
	 * Answer an array of the Agent ids that are the instructors for the course.
	 * 
	 * @return array 
	 * @access public
	 * @since 8/20/07
	 */
	public function getInstructors () {
		$instructors = array();
		foreach ($this->sections as $section) {
			$instructors = array_merge($instructors, $section->getInstructors());
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
		foreach ($this->sections as $section) {
			$students = array_merge($students, $section->getStudents());
		}
		return $students;
	}
}

?>