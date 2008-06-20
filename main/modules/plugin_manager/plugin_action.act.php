<?php
/**
 * @since 6/19/08
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
// require_once(MYDIR."/main/library/Plugins/SeguePlugins/SeguePluginAction.interface.php");

/**
 * This action handles executing plugin-specific actions.
 * 
 * @since 6/19/08
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class plugin_actionAction
	extends Action
{
		
	/**
	 * Authorization Check
	 * 
	 * @return bolean
	 * @access public
	 * @since 6/19/08
	 */
	public function isAuthorizedToExecute () {
		$action = $this->getAction();
		if ($action->isPerInstance())
			return $this->getPluginInstance()->canView();
		else
			return true;
	}
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 6/19/08
	 */
	public function execute () {
		
		exit;
	}
	
	/**
	 * Answer the instance of the plugin
	 * 
	 * @return object SeguePluginsPlugin
	 * @access protected
	 * @since 6/19/08
	 */
	protected function getPluginInstance () {
		$pluginMgr = Services::getService("PluginManager");
		$pluginMgr->getPluginDir(HarmoniType::fromString(RequestContext::value('plugin')));
	}
	
}

?>