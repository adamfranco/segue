<?php
/**
 * @since 7/28/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/XmlAction.class.php');

/**
 * Set a session var for the selected slot.
 * 
 * @since 7/28/08
 * @package segue.portal
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class deselect_for_copyAction
	extends XmlAction
{
	
	/**
	 * Check that the user is authorized to select the slot
	 * 
	 * @return boolean
	 * @access public
	 * @since 7/28/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return void
	 * @access public
	 * @since 7/28/08
	 */
	public function execute () {
		$this->start();
		try {
		
			if (!$this->isAuthorizedToExecute())
				$this->error(_("Your are not authorized to select this site."), "PermissionDenied");
			
			unset($_SESSION['portal_slot_selection']);
			print "success";
		
		} catch (Exception $e) {
			$this->error($e->getMessage(), get_class($e));
		}
		$this->end();
	}
}

?>