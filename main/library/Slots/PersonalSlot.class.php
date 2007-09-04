<?php
/**
 * @since 8/14/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PersonalSlot.class.php,v 1.4 2007/09/04 17:38:42 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/Slot.abstract.php");

/**
 * A single personal slot appears by default for all users that are members of 
 * the groups designated.
 * 
 * @since 8/14/07
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PersonalSlot.class.php,v 1.4 2007/09/04 17:38:42 adamfranco Exp $
 */
class PersonalSlot
	extends Slot
{
	
	/**
	 * @var array $validGroups;  
	 * @access private
	 * @since 8/16/07
	 */
	public static $validGroups = array();
	
	/**
	 * Answer the type of slot for this instance
	 * 
	 * @return string
	 * @access public
	 * @since 8/14/07
	 */
	public function getType () {
		return "personal";
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
		$slots = array();
		if (self::hasPersonal()) {
			$authN = Services::getService("AuthN");
			$userId = $authN->getFirstUserId();
			$slot = new PersonalSlot(self::getPersonalShortname($userId));
			$slot->populateOwnerId($userId);
			
			$slots[] = $slot;
		}
		
		return $slots;
	}
	
	/**
	 * Answer true if the user can create personal slots
	 * 
	 * @return boolean
	 * @access public
	 * @static
	 * @since 8/23/07
	 */
	public static function hasPersonal () {
		$authN = Services::getService("AuthN");
		$idManager = Services::getService("Id");
		$agentManager = Services::getService("Agent");
		
		$userId = $authN->getFirstUserId();
		
		if (!$userId->isEqual($idManager->getId("edu.middlebury.agents.anonymous"))) {
			// Match the groups the user is in against our configuration of
			// groups whose members should have personal sites.
			$ancestorSearchType = new HarmoniType("Agent & Group Search",
													"edu.middlebury.harmoni","AncestorGroups");
			$containingGroups = $agentManager->getGroupsBySearch(
							$userId, $ancestorSearchType);
			
			while ($containingGroups->hasNext()) {
				$group = $containingGroups->next();
				foreach (self::$validGroups as $validGroupId) {
					if ($validGroupId->isEqual($group->getId())) {
						return true;
					}
				}
			}
		}
		
		return false;
	}
	
	/**
	 * Answer the user-shortname for an agentId
	 * 
	 * @param object Id $agentId
	 * @return string
	 * @access public
	 * @since 8/22/07
	 */
	public function getPersonalShortname (Id $agentId) {
		$agentManager = Services::getService("Agent");
		$agent = $agentManager->getAgent($agentId);
		
		$properties = $agent->getProperties();		
		$email = null;
		while ($properties->hasNext() && !$email) {
			$email = $properties->next()->getProperty("email");
		}
		
		if (!$email)
			throw new Exception("No email found for agentId, '$agentId'.");
		
		return substr($email, 0, strpos($email, '@'));
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