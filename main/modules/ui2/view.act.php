<?php
/**
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.27 2008/03/18 20:25:30 achapin Exp $
 */ 
 
require_once(POLYPHONY.'/main/library/AbstractActions/Action.class.php');

/**
 * Test view using new components
 * 
 * @since 4/3/06
 * @package segue.modules.site
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.27 2008/03/18 20:25:30 achapin Exp $
 */
class viewAction
	extends Action 
{
	
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 10/24/07
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute the Action
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/3/06
	 */
	function execute () {
		// redirect
		$harmoni = Harmoni::instance();
		$newUrl = $harmoni->request->mkURLWithPassthrough('view', 'html');
		RequestContext::sendTo($newUrl->write());
	}
}

?>