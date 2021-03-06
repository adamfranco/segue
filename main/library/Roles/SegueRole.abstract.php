<?php
/**
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueRole.abstract.php,v 1.6 2008/04/11 19:33:28 adamfranco Exp $
 */ 

/**
 * The abstract SegueRole class is extended by the various concrete Roles.
 * 
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueRole.abstract.php,v 1.6 2008/04/11 19:33:28 adamfranco Exp $
 */
abstract class SegueRole 
	extends Magnitude
{
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 11/5/07
	 */
	public function __construct () {
		$this->functions = array();
	}
	
	/**
	 * Answer an IdString that identifies this role.
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	abstract public function getIdString ();
	
	/**
	 * Answer the display name of this role
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	abstract public function getDisplayName ();
	
	/**
	 * Answer a description of this role
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	abstract public function getDescription ();
	
	/**
	 * Answer the functions that this role includes
	 * 
	 * @return array
	 * @access public
	 * @since 11/5/07
	 */
	public function getFunctions () {
		return $this->functions;
	}
	
	/**
	 * Answer true if the array of AuthorizationFunctions passed matches this role
	 * 
	 * @param array $functions An array of AuthorizationFunctions
	 * @return boolean
	 * @access public
	 * @since 11/5/07
	 */
	public function matches (array $functions) {
		foreach ($functions as $func) {
			if ($this->functionConflicts($func))
				return false;
		}
		
		foreach ($this->getFunctions() as $myFunc) {
			$exists = false;
			foreach ($functions as $func) {
				if ($myFunc->isEqual($func)) {
					$exists = true;
					break;
				}
			}
			
			if (!$exists)
				return false;
		}
		
		return true;
	}
	
	/**
	 * Apply the role for the current user.
	 * 
	 * @param object Id $qualifierId
	 * @param optional boolean $overrideAzCheck If true, not not check AZs. Used by admin functions to force-set a role.
	 * @return void
	 * @access public
	 * @since 11/9/07
	 */
	public function applyToUser (Id $qualifierId, $overrideAzCheck = false) {
		$authN = Services::getService("AuthN");
		return $this->apply($authN->getFirstUserId(), $qualifierId, $overrideAzCheck);
	}
	
	/**
	 * Set authorizations to apply this role for an Agent at a Qualifier.
	 *
	 * Explicit Authorizations for the Agent at the Qualifier will be removed
	 * and added in order to apply the role.
	 * 
	 * Implicit Authorizations will not be changed.
	 * 
	 * @param object Id $agentId
	 * @param object Id $qualifierId
	 * @param optional boolean $overrideAzCheck If true, not not check AZs. Used by admin functions to force-set a role.
	 * @return void
	 * @access public
	 * @since 11/5/07
	 */
	public function apply (Id $agentId, Id $qualifierId, $overrideAzCheck = false) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		if (!$overrideAzCheck) {
			if (!$authZ->isUserAuthorized(
					$idMgr->getId("edu.middlebury.authorization.modify_authorizations"),
					$qualifierId))
				throw new PermissionDeniedException("Cannot modify authorizations here.");
		}
		
		/*********************************************************
		 * Split apart Everyone roles into 'reader' for everyone and the higher role
		 * for Users who are logged in (everyone except anonymous).
		 *
		 * Search for the string 'only-logged-in-can-edit' to find other code that
		 * makes this effect happen.
		 *********************************************************/
		$everyoneId = $idMgr->getId('edu.middlebury.agents.everyone');
		$usersId = $idMgr->getId('edu.middlebury.agents.users');
		if ($agentId->isEqual($everyoneId)) {
			$commenterRole = new Commenter_SegueRole;
			// if the role is more than just viewing, give everyone view and
			// users the rest.
			if ($this->isGreaterThan($commenterRole)) {
				$commenterRole->apply($everyoneId, $qualifierId, $overrideAzCheck);
				$this->apply($usersId, $qualifierId, $overrideAzCheck);
				return;
			}
			// If the role is view or less revoke roles from users and continue on
			// to set this role for everyone.
			else {
				$noAccessRole = new NoAccess_SegueRole;
				$noAccessRole->apply($usersId, $qualifierId, $overrideAzCheck);
				// Continue on to set the role for everyone.
			}
		}
		/*********************************************************
		 * End only-logged-in-can-edit
		 *********************************************************/
		
		
		$authorizations = $authZ->getExplicitAZs($agentId, null, $qualifierId, true);
		
		// Delete Conflicting functions. We leave functions that the roles don't know about.
		$existing = array();
		while ($authorizations->hasNext()) {
			$authorization = $authorizations->next();
			if ($this->functionConflicts($authorization->getFunction()->getId()))
				$authZ->deleteAuthorization($authorization);
			else if ($this->hasFunction($authorization->getFunction()->getId()))
				$existing[] = $authorization->getFunction()->getId();
		}
		
		// Add in new needed functions
		foreach ($this->getFunctions() as $func) {
			$this->addAuthorizationForFunction($agentId, $func, $qualifierId, $existing);
		}
	}
	
	/**
	 * Answer true if this role includes a given Authorization Function
	 * 
	 * @param object Id $functionId
	 * @return boolean
	 * @access public
	 * @since 11/5/07
	 */
	public function hasFunction (Id $functionId) {
		foreach ($this->getFunctions() as $func) {
			if ($functionId->isEqual($func))
				return true;
		}
		
		return false;
	}
	
	/**
	 * Answer true if this role cannot have a given Authorization Function.
	 * Roles are hierarchical in that higher-level roles are super-sets of
	 * lower-level rows. The hierarchy always has one or zero children.
	 * Any Roles that aren't in the current one, but are in one of its parents,
	 * then it by definition conflicts with this role. Functions not included
	 * in any role do not conflict and are ignored.
	 * 
	 * @param object Id $functionId
	 * @return boolean
	 * @access public
	 * @since 11/5/07
	 */
	public function functionConflicts (Id $functionId) {
		if ($this->hasFunction($functionId))
			return false;
		
		$mgr = SegueRoleManager::instance();
		$aboveMe = false;
		foreach ($mgr->getRoles() as $role) {
			if ($aboveMe) {
				if ($role->hasFunction($functionId))
					return true;
			} else if ($role->getIdString() == $this->getIdString()) {
				$aboveMe = true;
			}
		}
		
		return false;
	}
	
	/**
	 * Answer true if this role is equal to the role passed
	 * 
	 * @param object SegueRole $role
	 * @return boolean
	 * @access public
	 * @since 11/5/07
	 */
	public function isEqualTo (SegueRole $role) {
		if ($role->getIdString() == $this->getIdString())
			return true;
		
		return false;
	}
	
	/**
	 * Answer true if this role is a sub-set of the role passed
	 * 
	 * @param object SegueRole $role
	 * @return boolean
	 * @access public
	 * @since 11/5/07
	 */
	public function isLessThan ( $role) {
		if (!($role instanceof SegueRole))
			throw new Exception("Parameter must be a role.");
		
		if ($this->isEqualTo($role))
			return false;
		
		$mgr = SegueRoleManager::instance();
		foreach($mgr->getRoles() as $currentRole) {
			// If we first hit the us rule, before hitting the other rule, then we are less.
			if ($this->getIdString() == $currentRole->getIdString())
				return true;
			else if ($role->getIdString() == $currentRole->getIdString())
				return false;
		}
		
		throw new Exception("Unknown Role '".$role->getIdString()."'.");
	}
	
	/**
	 * Ask the role to store a list of agent Ids which caused the role to exist.
	 * This should be used only by the SegueRoleManager. It is intended to be a
	 * temparary store which may or may not be populated when roles are returned.
	 * 
	 * @param array $agentIds
	 * @return void
	 * @access public
	 * @since 11/28/07
	 */
	public function setAgentsCausing (array $agentIds) {
		ArgumentValidator::validate($agentIds, ArrayValidatorRuleWithRule::getRule(
			ExtendsValidatorRule::getRule("Id")));
		
		$idStrings = array();
		$this->agentsCausing = array();
		foreach ($agentIds as $id) {
			if (!in_array($id->getIdString(), $idStrings)) {
				$this->agentsCausing[] = $id;
				$idStrings[] = $id->getIdString();
			}
		}
	}
	
	/**
	 * Answer a list of agent Ids which caused this role to exist. This list
	 * may or may not be available and is to be used for informational purposes
	 * rather than as a definitive list. If this list has not been populated,
	 * an Exception will be thrown.
	 * 
	 * @return array
	 * @access public
	 * @since 11/28/07
	 */
	public function getAgentsCausing () {
		if (!isset($this->agentsCausing) || !is_array($this->agentsCausing))
			throw new Exception("A list of Agents-Causing this role has not been set.");
		
		return $this->agentsCausing;
	}
	
	/*********************************************************
	 * Protected
	 *********************************************************/
	
	/**
	 * Add a new function to this role
	 * 
	 * @param object Id $functionId
	 * @return void
	 * @access public
	 * @since 11/5/07
	 */
	public function addFunction (Id $functionId) {
		$this->functions[] = $functionId;
	}
	

	/*********************************************************
	 * Private
	 *********************************************************/
	
	/**
	 * @var array $functions;  
	 * @access private
	 * @since 11/5/07
	 */
	private $functions;
	
	/**
	 * @var array $agentsCausing;  
	 * @access private
	 * @since 11/28/07
	 */
	private $agentsCausing;
	
	/**
	 * Add an authorization
	 * 
	 * @param object Id $agentId
	 * @param object Id $functionId
	 * @param object Id $qualifierId
	 * @param array $appliedFunctions An array of function ids
	 * @return void
	 * @access protected
	 * @since 11/5/07
	 */
	protected function addAuthorizationForFunction (Id $agentId, Id $functionId, Id $qualifierId, array $appliedFunctions = array()) {
		foreach ($appliedFunctions as $func) {
			if ($functionId->isEqual($func))
				return;
		}
		
		$authZ = Services::getService("AuthZ");
		$authZ->createAuthorization($agentId, $functionId, $qualifierId);
	}
}

?>