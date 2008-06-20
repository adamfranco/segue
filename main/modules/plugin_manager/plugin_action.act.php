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
require_once(MYDIR."/main/library/PluginManager/SeguePlugins/SeguePluginsAction.interface.php");

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
		if (call_user_func(array($this->getActionClassName(), 'isPerInstance')))
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
		if (!$this->isAuthorizedToExecute())
			throw new PermissionDeniedException(_("You are not authorized to execute this action"));
		
		$this->getAction()->execute();
		exit;
	}
	
	/**
	 * Answer the action instance to execute
	 * 
	 * @return object SeguePluginsAction
	 * @access protected
	 * @since 6/19/08
	 */
	protected function getAction () {
		if (!isset($this->_action)) {
			$class = $this->getActionClassName();
			
			$action = new $class();
			if (!$action instanceof SeguePluginsAction)
				throw new UnknownActionException("'".$class."' is not a valid plugin action. Does not implement interface: SeguePluginsAction.");
			
			$harmoni = Harmoni::instance();
			$restricted = array('module', 'action', 'plugin', 'paction', 'plugin_id');
			$params = array();
			foreach ($harmoni->request->getKeys() as $key) {
				if (!in_array($key, $restricted))
					$params[$key] = RequestContext::value($key);
			}
			$action->setRequestParams($params);
			
			if (call_user_func(array($this->getActionClassName(), 'isPerInstance')))
				$action->setPluginInstance($this->getPluginInstance());
			
			$this->_action = $action;
		}
		
		return $this->_action;
	}
	
	/**
	 * Answer the action class name
	 * 
	 * @return string
	 * @access protected
	 * @since 6/19/08
	 */
	protected function getActionClassName () {
		if (!isset($this->_actionClass)) {
			$class = RequestContext::value('paction');
			if (!preg_match('/^[a-z0-9_]+$/i', $class))
				throw new InvalidArgumentException("'".$class."' is not a valid plugin action. Invalid class name.");
			
			$actionFile = $this->getPluginDir().'/'.$class.'.act.php';
			if (!file_exists($actionFile))
				throw new UnknownActionException("'".$class."' is not a valid plugin action. No action file exists.");
			
			require_once($actionFile);
			
			if (!class_exists($class))
				throw new UnknownActionException("'".$class."' is not a valid plugin action. Action class, '".$class."' does not exist.");
			
			$this->_actionClass = $class;
		}
		
		return $this->_actionClass;
	}
	
	/**
	 * Answer the directory for the plugin
	 * 
	 * @return string
	 * @access protected
	 * @since 6/18/08
	 */
	protected function getPluginDir () {
		$pluginMgr = Services::getService("PluginManager");
		$dir = rtrim($pluginMgr->getPluginDir(
			HarmoniType::fromString(RequestContext::value('plugin'))), '/');
		
		if (!file_exists($dir))
			throw new Exception('Unknown Plugin "'.RequestContext::value('plugin').'".');
		
		return $dir;
	}
	
	/**
	 * Answer the instance of the plugin
	 * 
	 * @return object SeguePluginsPlugin
	 * @access protected
	 * @since 6/19/08
	 */
	protected function getPluginInstance () {
		if (!isset($this->_pluginInstance)) {
			$id = RequestContext::value('plugin_id');
			if (!$id)
				throw new InvalidArgumentException("No Id specified.");
			$pluginMgr = Services::getService("PluginManager");
			
			$repositoryManager = Services::getService("Repository");
			$idManager = Services::getService("Id");
			$repository = $repositoryManager->getRepository(
				$idManager->getId("edu.middlebury.segue.sites_repository"));
			
			$asset = $repository->getAsset($idManager->getId($id));
			
			$this->_pluginInstance = $pluginMgr->getPlugin($asset);
		}
		
		return $this->_pluginInstance;
	}
	
}

?>