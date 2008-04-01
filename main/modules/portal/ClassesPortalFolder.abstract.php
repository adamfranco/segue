<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ClassesPortalFolder.abstract.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/PortalFolder.interface.php");

/**
 * An abstract class for classes-based folders
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ClassesPortalFolder.abstract.php,v 1.1 2008/04/01 20:32:49 adamfranco Exp $
 */
abstract class ClassesPortalFolder
	implements PortalFolder 
{
		
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
	 * Answer an array of the slots in this category.
	 * 
	 * @return array of Slot objects
	 * @access public
	 * @since 4/1/08
	 */
	public function getSlots () {
		$slotMgr = SlotManager::instance();
		$courses = $this->getCourses();
		$slots = array();
		if (count($courses)) {
			foreach ($courses as $course) {
				$slots[] = $slotMgr->getSlotByShortname($course->getId()->getIdString());
			}
		}
		
		return $slots;
	}
	
	/**
	 * Answer an array of courses for this folder
	 * 
	 * @return array of course objects
	 * @access protected
	 * @since 4/1/08
	 */
	abstract protected function getCourses ();
	
	/**
	 * Answer a string of controls html to go along with this folder. In many cases
	 * it will be empty, but some implementations may need controls for adding new slots.
	 * 
	 * @return string
	 * @access public
	 * @since 4/1/08
	 */
	public function getControlsHtml () {
		return '';
	}
	
}

?>