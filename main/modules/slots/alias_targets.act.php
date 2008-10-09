<?php
/**
 * @since 10/9/08
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * This action will return a listing of matching slots for making target aliases.
 * 
 * @since 10/9/08
 * @package segue.slots
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class alias_targetsAction
	extends Action
{
		
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 10/9/08
	 */
	public function isAuthorizedToExecute () {
		$authN = Services::getService('AuthN');
		return $authN->isUserAuthenticatedWithAnyType();
	}
	
	/**
	 * Execute the action
	 * 
	 * @return void
	 * @access public
	 * @since 10/9/08
	 */
	public function execute () {
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException("You must be logged in.");
		
		header('Content-Type: text/xml;');
		print "<ul>";
		$search = RequestContext::value('search');
		if ($search && preg_match('/^[a-z0-9\._-]+$/i', $search)) {
			$slots = SlotManager::instance()->getSlotsWithSitesBySearch($search.'*');
			foreach ($slots as $slot)
				print "\n\t<li>".$slot->getShortName()."</li>";
		}
		print "\n</ul>";
		exit;
	}
	
}

?>