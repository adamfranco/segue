<?php
/**
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Commenter_SegueRole.class.php,v 1.1 2007/11/05 21:09:03 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueRole.abstract.php");

/**
 * The Comment_SegueRole also allows the adding of comments.
 * 
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Commenter_SegueRole.class.php,v 1.1 2007/11/05 21:09:03 adamfranco Exp $
 */
class Commenter_SegueRole
	extends SegueRole
{
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 11/5/07
	 */
	public function __construct () {
		parent::__construct();
		
		$idMgr = Services::getService("Id");
		$this->addFunction($idMgr->getId("edu.middlebury.authorization.view"));
		
		$this->addFunction($idMgr->getId("edu.middlebury.authorization.comment"));
		$this->addFunction($idMgr->getId("edu.middlebury.authorization.view_comments"));
	}
	
	/**
	 * Answer an IdString that identifies this role.
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getIdString () {
		return 'commenter';
	}
	
	/**
	 * Answer the display name of this role
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getDisplayName () {
		return _("Commenter");
	}
	
	/**
	 * Answer a description of this role
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getDescription () {
		return _("This role allows the viewing of items as well as commenting in discussions.");
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
		$everyoneId = $idMgr->getId('edu.middlebury.agents.everyone');
		
		if (!$agentId->isEqual($everyoneId))
			return parent::apply($agentId, $qualifierId, $overrideAzCheck);
		
		if (!$overrideAzCheck) {
			if (!$authZ->isUserAuthorized(
					$idMgr->getId("edu.middlebury.authorization.modify_authorizations"),
					$qualifierId))
				throw new PermissionDeniedException("Cannot modify authorizations here.");
		}
		/*********************************************************
		 * For this role, give the view and view_comments authorizations to 
		 * the 'everyone' group and the 'comment' authorization to
		 * the 'users' group to prevent anonymous posting.
		 *
		 * Search for the string 'only-logged-in-can-edit' to find other code that
		 * makes this effect happen.
		 *********************************************************/		
		// Run through the Authorizations for the 'everyone' group
		$authorizations = $authZ->getExplicitAZs($everyoneId, null, $qualifierId, true);
		
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
		$this->addAuthorizationForFunction($everyoneId, $idMgr->getId("edu.middlebury.authorization.view"), $qualifierId, $existing);
		$this->addAuthorizationForFunction($everyoneId, $idMgr->getId("edu.middlebury.authorization.view_comments"), $qualifierId, $existing);
		
		
		// Run through the Authorizations for the 'users' group
		$usersId = $idMgr->getId('edu.middlebury.agents.users');
		$authorizations = $authZ->getExplicitAZs($usersId, null, $qualifierId, true);
		
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
		$this->addAuthorizationForFunction($usersId, $idMgr->getId("edu.middlebury.authorization.comment"), $qualifierId, $existing);
		/*********************************************************
		 * End only-logged-in-can-edit
		 *********************************************************/
	}
}

?>