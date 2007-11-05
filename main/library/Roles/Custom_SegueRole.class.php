<?php
/**
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Custom_SegueRole.class.php,v 1.1 2007/11/05 21:09:03 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SegueRole.abstract.php");

/**
 * The Reader_SegueRole is a view-only role.
 * 
 * @since 11/5/07
 * @package segue.authorization.roles
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Custom_SegueRole.class.php,v 1.1 2007/11/05 21:09:03 adamfranco Exp $
 */
class Custom_SegueRole
	extends SegueRole
{
	
	/**
	 * Answer an IdString that identifies this role.
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getIdString () {
		return 'custom';
	}
	
	/**
	 * Answer the display name of this role
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getDisplayName () {
		return _("Custom Role");
	}
	
	/**
	 * Answer a description of this role
	 * 
	 * @return string
	 * @access public
	 * @since 11/5/07
	 */
	public function getDescription () {
		return _("This role is a make-up of custom authorizations. Users with this role have some access, but in a combination that doesn't match one of the pre-defined roles. Applying this role will not change existing authorizations.");
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
		return true;
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
		return false;
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
	 * @return void
	 * @access public
	 * @since 11/5/07
	 */
	public function apply (Id $agentId, Id $qualifierId) {
		// do nothing
	}
}

?>