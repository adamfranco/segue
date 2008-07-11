<?php
/**
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueRoleManager.class.php,v 1.8 2007/12/03 22:00:14 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/NoAccess_SegueRole.class.php");
require_once(dirname(__FILE__)."/Reader_SegueRole.class.php");
require_once(dirname(__FILE__)."/Commenter_SegueRole.class.php");
require_once(dirname(__FILE__)."/Author_SegueRole.class.php");
require_once(dirname(__FILE__)."/Editor_SegueRole.class.php");
require_once(dirname(__FILE__)."/Admin_SegueRole.class.php");
require_once(dirname(__FILE__)."/Custom_SegueRole.class.php");

/**
 * The SegueAuthorizationManager wraps an O.K.I. AuthorizationManager and provides
 * access to a simplified hierarchy of roles. These roles are then stored as discrete
 * authorizations in the AuthorizationManager. Authorization checks are done against
 * the authorizations defined in the underlying AuthorizationManager.
 * 
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueRoleManager.class.php,v 1.8 2007/12/03 22:00:14 adamfranco Exp $
 */
class SegueRoleManager
	
{
	
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
			self::$instance = new SegueRoleManager;
		
		return self::$instance;
	}
	
	/*********************************************************
	 * Instance Vars and Methods
	 *********************************************************/
	
	/**
	 * @var array $roles;  
	 * @access private
	 * @since 11/5/07
	 */
	private $roles = array();

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access private
	 * @since 11/5/07
	 */
	private function __construct () {
		$this->roles[] = new NoAccess_SegueRole;
		$this->roles[] = new Reader_SegueRole;
		$this->roles[] = new Commenter_SegueRole;
		$this->roles[] = new Author_SegueRole;
		$this->roles[] = new Editor_SegueRole;
		$this->roles[] = new Admin_SegueRole;
		$this->roles[] = new Custom_SegueRole;
	}
	
// 	/**
// 	 * This is a 'Magic Method' that will pass all calls through to the 
// 	 * underlying AuthorizationManager.
// 	 * 
// 	 * @param string $method
// 	 * @param mixed $args
// 	 * @return mixed
// 	 * @access public
// 	 * @since 11/5/07
// 	 */
// 	public function __call ($method, $args) {
// 		$authZ = Services::getService("AuthZ");
// 		
// 		if (!method_exists($authZ, $method))
// 			throw new Exception("Unknown method '$method'.");
// 		
// 		return call_user_func_array(array($authZ, $method),	$args);
// 	}
	
	/**
	 * Answer the roles available
	 * 
	 * @return array
	 * @access public
	 * @since 11/5/07
	 */
	public function getRoles () {
		return $this->roles;
	}
	
	/**
	 * Answer a role by Id
	 * 
	 * @param string $idString
	 * @return object Role
	 * @access public
	 * @since 11/5/07
	 */
	public function getRole ($idString) {
		foreach ($this->getRoles() as $role) {
			if ($role->getIdString() == $idString)
				return $role;
		}
		
		throw new Exception ("Role '$idString' was not found.");
	}
	
	/**
	 * Clear out all authorizations managed as roles for the agent at the qualifier.
	 * 
	 * @param object Id $agentId
	 * @param object Id $qualifierId
	 * @return void
	 * @access public
	 * @since 11/5/07
	 */
	public function clearRoleAzs (Id $agentId, Id $qualifierId) {
		// Apply the NoAccess role
		$this->roles[0]->apply($agentId, $qualifierId);
		
		/*********************************************************
		 * If the agent specified is 'everyone', also clear roles for 'users'
		 *
		 * Search for the string 'only-logged-in-can-edit' to find other code that
		 * makes this effect happen.
		 *********************************************************/
		$idMgr = Services::getService("Id");
		$everyoneId = $idMgr->getId('edu.middlebury.agents.everyone');
		if ($agentId->isEqual($everyoneId)) {
			$usersId = $idMgr->getId('edu.middlebury.agents.users');
			$this->roles[0]->apply($usersId, $qualifierId);
		}
		/*********************************************************
		 * End only-logged-in-can-edit
		 *********************************************************/
	}
	
	/**
	 * Answer the role for the current User
	 * 
	 * @param object Id $qualifierId
	 * @param optional boolean $overrideAzCheck If true, not not check AZs. Used by admin functions to force-set a role.
	 * @return object Role
	 * @access public
	 * @since 11/9/07
	 */
	public function getUsersRole (Id $qualifierId, $overrideAzCheck = false) {
		$authN = Services::getService("AuthN");
		return $this->getAgentsRole($authN->getFirstUserId(), $qualifierId, $overrideAzCheck);
	}
	
	/**
	 * Answer the role at the given qualifier, regardless of whether it was set
	 * implicitly or explicitly.
	 * 
	 * @param object Id $agentId
	 * @param object Id $qualifierId
	 * @param optional boolean $overrideAzCheck If true, not not check AZs. Used by admin functions to force-set a role.
	 * @return object Role
	 * @access public
	 * @since 11/5/07
	 */
	public function getAgentsRole (Id $agentId, Id $qualifierId, $overrideAzCheck = false) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		if (!$overrideAzCheck) {
			if (!$authZ->isUserAuthorized(
					$idMgr->getId("edu.middlebury.authorization.view_authorizations"),
					$qualifierId))
				throw new PermissionDeniedException("Cannot view authorizations here.");
		}
	
		// Load the functions explicitly set for this agent at this qualifier
		$authorizations = $authZ->getAllAZs($agentId, null, $qualifierId, true);
		
		/*********************************************************
		 * For the 'everyone' group will will also add in AZs from the 'users'
		 * group to make it look like AZs are being set for 'everyone' while
		 * allowing only logged-in users to make changes.
		 *
		 * Search for the string 'only-logged-in-can-edit' to find other code that
		 * makes this effect happen.
		 *********************************************************/
		$idMgr = Services::getService("Id");
		$everyoneId = $idMgr->getId('edu.middlebury.agents.everyone');
		if ($agentId->isEqual($everyoneId)) {
			$usersId = $idMgr->getId('edu.middlebury.agents.users');
			$everyoneAZs = $authorizations;
			$authorizations = new MultiIteratorIterator;
			$authorizations->addIterator($everyoneAZs);
			$authorizations->addIterator($authZ->getAllAZs($usersId, null, $qualifierId, true));
		}
		/*********************************************************
		 * End only-logged-in-can-edit
		 *********************************************************/
		
		$functions = array();
		while ($authorizations->hasNext()) {
			$authorization = $authorizations->next();
			$functions[] = $authorization->getFunction()->getId();
		}
		
		// Match those authorizations against our roles.
		foreach($this->getRoles() as $role) {
			if ($role->matches($functions))
				return $role;
		}
		
		throw new Exception ("No matching Role was found. Custom should have matched, but didn't.");
	}
	
	/**
	 * Answer the role set explicitly at the given qualifier.
	 * 
	 * @param object Id $agentId
	 * @param object Id $qualifierId
	 * @param optional boolean $overrideAzCheck If true, not not check AZs. Used by admin functions to force-set a role.

	 * @return object Role
	 * @access public
	 * @since 11/5/07
	 */
	public function getAgentsExplicitRole (Id $agentId, Id $qualifierId, $overrideAzCheck = false) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		if (!$overrideAzCheck) {
			if (!$authZ->isUserAuthorized(
					$idMgr->getId("edu.middlebury.authorization.view_authorizations"),
					$qualifierId))
				throw new PermissionDeniedException("Cannot view authorizations here.");
		}
	
		// Load the functions explicitly set for this agent at this qualifier
		$authZ = Services::getService("AuthZ");
		$authorizations = $authZ->getExplicitAZs($agentId, null, $qualifierId, true);
		
		/*********************************************************
		 * For the 'everyone' group will will also add in AZs from the 'users'
		 * group to make it look like AZs are being set for 'everyone' while
		 * allowing only logged-in users to make changes.
		 *
		 * Search for the string 'only-logged-in-can-edit' to find other code that
		 * makes this effect happen.
		 *********************************************************/
		$idMgr = Services::getService("Id");
		$everyoneId = $idMgr->getId('edu.middlebury.agents.everyone');
		if ($agentId->isEqual($everyoneId)) {
			$usersId = $idMgr->getId('edu.middlebury.agents.users');
			$everyoneAZs = $authorizations;
			$authorizations = new MultiIteratorIterator;
			$authorizations->addIterator($everyoneAZs);
			$authorizations->addIterator($authZ->getExplicitAZs($usersId, null, $qualifierId, true));
		}
		/*********************************************************
		 * End only-logged-in-can-edit
		 *********************************************************/
		
		$functions = array();
		while ($authorizations->hasNext()) {
			$authorization = $authorizations->next();
			$functions[] = $authorization->getFunction()->getId();
		}
		
		// Match those authorizations against our roles.
		foreach($this->getRoles() as $role) {
			if ($role->matches($functions))
				return $role;
		}
		
		throw new Exception ("No matching Role was found. Custom should have matched, but didn't.");
	}
	
	/**
	 * Answer the role set implicitly at the given qualifier by cascading authorizations.
	 * 
	 * @param object Id $agentId
	 * @param object Id $qualifierId
	 * @param optional boolean $overrideAzCheck If true, not not check AZs. Used by admin functions to force-set a role.
	 * @return object Role
	 * @access public
	 * @since 11/5/07
	 */
	public function getAgentsImplicitRole (Id $agentId, Id $qualifierId, $overrideAzCheck = false) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		if (!$overrideAzCheck) {
			if (!$authZ->isUserAuthorized(
					$idMgr->getId("edu.middlebury.authorization.view_authorizations"),
					$qualifierId))
				throw new PermissionDeniedException("Cannot view authorizations here.");
		}
	
		// Load the functions explicitly set for this agent at this qualifier
		$authZ = Services::getService("AuthZ");
		$authorizations = $authZ->getAllAZs($agentId, null, $qualifierId, true);
		$functions = array();
		while ($authorizations->hasNext()) {
			$authorization = $authorizations->next();
			if (!$authorization->isExplicit())
				$functions[] = $authorization->getFunction()->getId();
		}
		
		// Match those authorizations against our roles.
		foreach($this->getRoles() as $role) {
			if ($role->matches($functions))
				return $role;
		}
		
		throw new Exception ("No matching Role was found. Custom should have matched, but didn't.");
	}
	
	
	/**
	 * Answer the implicit role caused by group membership.
	 *
	 * This is needed by a role-setting UI that needs to prevent the setting of
	 * roles less than those implicitly given elsewhere.
	 * 
	 * @param object Id $agentId
	 * @param object Id $qualifierId
	 * @param optional boolean $overrideAzCheck If true, not not check AZs. Used by admin functions to force-set a role.
	 * @return object Role
	 * @access public
	 * @since 11/26/07
	 */
	public function getGroupImplictRole (Id $agentId, Id $qualifierId, $overrideAzCheck = false) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		if (!$overrideAzCheck) {
			if (!$authZ->isUserAuthorized(
					$idMgr->getId("edu.middlebury.authorization.view_authorizations"),
					$qualifierId))
				throw new PermissionDeniedException("Cannot view authorizations here.");
		}
	
		// Load the functions explicitly set for this agent at this qualifier
		$authZ = Services::getService("AuthZ");
		$authorizations = $authZ->getAllAZs($agentId, null, $qualifierId, true);
		$functions = array();
		$groupIds = array();
		while ($authorizations->hasNext()) {
			$authorization = $authorizations->next();
			if (!$authorization->isExplicit()) {
				// Go through the explicit AZs that gave rize to the implicit AZ 
				// and record their function if the Agent Id is not the agent we're looking
				// for and therefor a group the agent is a member of.
				$explicitAZs = $authZ->getExplicitUserAZsForImplicitAZ($authorization);
				while ($explicitAZs->hasNext()) {
					$explicitAZ = $explicitAZs->next();
					if (!$agentId->isEqual($explicitAZ->getAgentId())) {
						$functions[] = $authorization->getFunction()->getId();
						$groupIds[] = $explicitAZ->getAgentId();
						break;	
					}
				}
			}
		}
		
		// Match those authorizations against our roles.
		foreach($this->getRoles() as $role) {
			if ($role->matches($functions)) {
				$role->setAgentsCausing($groupIds);
				return $role;
			}
		}
		
		throw new Exception ("No matching Role was found. Custom should have matched, but didn't.");
	}
	
	/**
	 * Answer the agents that have roles that are greater than or equal to the role passed.
	 * 
	 * @param object SegueRole $role
	 * @param object Id $rootQualifierId
	 * @param optional boolean $overrideAzCheck If true, not not check AZs. Used by admin functions to force-set a role.
	 * @return array An array of Id objects
	 * @access public
	 * @since 11/29/07
	 */
	public function getAgentsWithRoleAtLeast (SegueRole $role, Id $rootQualifierId, $overrideAzCheck = false) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		if (!$overrideAzCheck) {
			if (!$authZ->isUserAuthorized(
					$idMgr->getId("edu.middlebury.authorization.view_authorizations"),
					$rootQualifierId))
				throw new PermissionDeniedException("Cannot view authorizations here.");
		}
		
		// Get a list of all of the qualifiers in the site.
		$qualifiers = new MultiIteratorIterator;
		$qualifiers->addIterator(new HarmoniIterator(array($authZ->getQualifier($rootQualifierId))));
		$qualifiers->addIterator($authZ->getQualifierDescendants($rootQualifierId));
		
		// Go through each qualifier and see who can do all of the functions in the role
		$agentIdStrings = array();
		while ($qualifiers->hasNext()) {
			$qualifier = $qualifiers->next();
			$qualifierId = $qualifier->getId();
			
			// Build up an array of what agents can do each function
			$agentsForFunctions = array();
			foreach ($role->getFunctions() as $functionId) {
				$agentsForFunctions[$functionId->getIdString()] = array();
				$agentIds = $authZ->getWhoCanDo($functionId, $qualifierId);
				while ($agentIds->hasNext()) {
					$agentIdString = $agentIds->next()->getIdString();
					if (!in_array($agentIdString, $agentIdStrings))
						$agentsForFunctions[$functionId->getIdString()][] = $agentIdString;
				}
			}
			
			// Loop through the agents that can do the first function, if they can
			// do all the others, then they match the role and can be added to the master list.
			foreach (current($agentsForFunctions) as $agentIdString) {
				$hasAllFunctions = true;
				foreach ($role->getFunctions() as $functionId) {
					if (!in_array($agentIdString, $agentsForFunctions[$functionId->getIdString()])) {
						$hasAllFunctions = false;
						break;
					}
				}
				
				if ($hasAllFunctions)
					$agentIdStrings[] = $agentIdString;
			}
		}
		
		$agentIdStrings = array_unique($agentIdStrings);
		
		$agentIds = array();
		foreach ($agentIdStrings as $idString) {
			$agentIds[] = $idMgr->getId($idString);
		}
		
		return $agentIds;
	}
}

?>