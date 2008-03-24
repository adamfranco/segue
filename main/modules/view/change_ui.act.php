<?php
/**
 * @since 3/18/08
 * @package segue.modules.view
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: change_ui.act.php,v 1.3 2008/03/24 23:01:36 achapin Exp $
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/Action.class.php');

/**
 * This action sets the current UI mode and then returns to the previous location
 * 
 * @since 3/18/08
 * @package segue.modules.view
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: change_ui.act.php,v 1.3 2008/03/24 23:01:36 achapin Exp $
 */
class change_uiAction
	extends Action
{

	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/18/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
		
	/**
	 * Execture this action
	 * 
	 * @return void
	 * @access public
	 * @since 3/18/08
	 */
	public function execute () {
		$harmoni = Harmoni::instance();
		
		if (RequestContext::value('user_interface')) {
			$this->setUiModule(RequestContext::value('user_interface'));
		}
		
		$returnUrl = rawurldecode(RequestContext::value('returnUrl'));
		$oldModule = $harmoni->request->getModuleFromUrl ($returnUrl);
		$oldAction = $harmoni->request->getActionFromUrl ($returnUrl);
		$newModule = RequestContext::value('user_interface');
		
		if ($oldModule != $newModule && $oldModule != "view") {
			if ($oldAction == "arrangeview") {
				$returnUrl = str_replace($oldAction, "editview", $returnUrl);
			} 
			$returnUrl = str_replace($oldModule, $newModule, $returnUrl);			
		}		
		RequestContext::sendTo($returnUrl);	
	}
	
	/**
	 * Answer the current UI module
	 * 
	 * @return string
	 * @access public
	 * @since 7/27/07
	 */
	function getUiModule () {
		if (!isset($_SESSION['UI_MODULE']))
			$this->setUiModule('ui1');
			
		return $_SESSION['UI_MODULE'];
	}

	
	/**
	 * Set the UI module
	 * 
	 * @param string $module
	 * @return void
	 * @access public
	 * @since 7/27/07
	 */
	function setUiModule ($module) {
		$_SESSION['UI_MODULE'] = $module;
	}
	
}

?>