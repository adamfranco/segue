<?php
/**
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PortalManager.class.php,v 1.1 2008/04/01 20:32:50 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/MainPortalCategory.class.php");
require_once(dirname(__FILE__)."/AccessPortalCategory.class.php");
require_once(dirname(__FILE__)."/ClassesPortalCategory.class.php");

/**
 * The PortalManager provides a central access point to portal categories and folders.
 * 
 * @since 4/1/08
 * @package segue.modules.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PortalManager.class.php,v 1.1 2008/04/01 20:32:50 adamfranco Exp $
 */
class PortalManager {
		
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
			self::$instance = new PortalManager;
		
		return self::$instance;
	}
	
	/**
	 * @var array $categories;  
	 * @access private
	 * @since 4/1/08
	 */
	private $categories;
	
	/**
	 * Constructor, private to make sure that no one build this object like this:
	 * <code>$slotManager = new SlotManager()</code>
	 *
	 * @return void
	 * @access private
	 * @since 8/14/07
	 */
	private function __construct() {
		$this->categories = array(
			new MainPortalCategory,
			new ClassesPortalCategory,
			new AccessPortalCategory
			
		);
	}
	
	/**
	 * Answer an array categories
	 * 
	 * @return array of PortalCategory objects
	 * @access public
	 * @since 4/1/08
	 */
	public function getCategories () {
		return $this->categories;
	}
	
	/**
	 * Answer a category by id-string
	 * 
	 * @param string $idString
	 * @return object PortalCategory
	 * @access public
	 * @since 4/1/08
	 */
	public function getCategory ($idString) {
		foreach ($this->getCategories() as $category) {
			if ($category->getIdString() == $idString)
				return $category;
		}
		
		throw new UnknownIdException("No category with id, '$idString', exists here.");
	}
	
	/**
	 * Answer a folder by Id-string
	 * 
	 * @param string $idString
	 * @return object PortalFolder
	 * @access public
	 * @since 4/1/08
	 */
	public function getFolder ($idString) {
		foreach ($this->getCategories() as $category) {
			try {
				return $category->getFolder($idString);
			} catch (UnknownIdException $e) {
			}
		}
		
		throw new UnknownIdException("No category with id, '$idString', exists here.");
	}
}

?>