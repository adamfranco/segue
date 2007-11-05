<?php
/**
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SegueRoleManager.class.php,v 1.2 2007/11/05 21:46:43 adamfranco Exp $
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
 * @version $Id: SegueRoleManager.class.php,v 1.2 2007/11/05 21:46:43 adamfranco Exp $
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
	 * @access public
	 * @since 11/5/07
	 */
	public function __construct () {
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
	}
	
	/**
	 * Answer the role at the given qualifier, regardless of whether it was set
	 * implicitly or explicitly.
	 * 
	 * @param object Id $agentId
	 * @param object Id $qualifierId
	 * @return object Role
	 * @access public
	 * @since 11/5/07
	 */
	public function getAgentsRole (Id $agentId, Id $qualifierId) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		if (!$authZ->isUserAuthorized(
				$idMgr->getId("edu.middlebury.authorization.view_authorizations"),
				$qualifierId))
			throw new PermissionDeniedException("Cannot view authorizations here.");
	
		// Load the functions explicitly set for this agent at this qualifier
		$authorizations = $authZ->getAllAZs($agentId, null, $qualifierId, true);
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
	 * @return object Role
	 * @access public
	 * @since 11/5/07
	 */
	public function getAgentsExplicitRole (Id $agentId, Id $qualifierId) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		if (!$authZ->isUserAuthorized(
				$idMgr->getId("edu.middlebury.authorization.view_authorizations"),
				$qualifierId))
			throw new PermissionDeniedException("Cannot view authorizations here.");
	
		// Load the functions explicitly set for this agent at this qualifier
		$authZ = Services::getService("AuthZ");
		$authorizations = $authZ->getExplicitAZs($agentId, null, $qualifierId, true);
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
	 * @return object Role
	 * @access public
	 * @since 11/5/07
	 */
	public function getAgentsImplicitRole (Id $agentId, Id $qualifierId) {
		$authZ = Services::getService("AuthZ");
		$idMgr = Services::getService("Id");
		
		if (!$authZ->isUserAuthorized(
				$idMgr->getId("edu.middlebury.authorization.view_authorizations"),
				$qualifierId))
			throw new PermissionDeniedException("Cannot view authorizations here.");
	
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
}

?>