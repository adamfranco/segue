<?php
/**
 * @since 8/14/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CustomSlot.class.php,v 1.3 2008/03/13 13:29:32 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/Slot.abstract.php");

/**
 * Custom slots are those that are defined manually rather than programatically.
 * 
 * @since 8/14/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CustomSlot.class.php,v 1.3 2008/03/13 13:29:32 adamfranco Exp $
 */
class CustomSlot
	extends SlotAbstract
{

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
		return "custom";
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
	 * Answer the external slots for the current user
	 * 
	 * @return array
	 * @access protected
	 * @static
	 * @since 8/14/07
	 */
	public static function getExternalSlotDefinitionsForUser () {
		return array();
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
	
	}
}

?>