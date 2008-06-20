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

/**
 * This interface defines plugin-specific actions.
 * 
 * @since 6/19/08
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
interface SeguePluginsAction {
		
	/**
	 * If this action should execute tied to a single plugin instance, return true.
	 * If true, a plugin instance will be passed to the plugin via its setPluginInstance()
	 * method.
	 *
	 * If this action is more general-purpose and not tied to a single plugin, return false.
	 * If false, no plugin instance will be availible to this action.
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/19/08
	 * @static
	 */
	public static function isPerInstance ();
	
	/**
	 * If this action is per-instance this method will be called to give the action
	 * its instance
	 * 
	 * @param object SeguePluginsAPI $pluginInstance
	 * @return void
	 * @access public
	 * @since 6/19/08
	 */
	public function setPluginInstance (SeguePluginsAPI $pluginInstance);
	
	/**
	 * This method will be called on the action to provide it with request params.
	 * Actions should not look at $_REQUEST, $_GET, or $_POST as alternate methods
	 * of passing or encoding parameters may be used.
	 * 
	 * @param array $requestParams
	 * @return void
	 * @access public
	 * @since 6/19/08
	 */
	public function setRequestParams (array $requestParams);
	
	/**
	 * Execute this action. The setX methods will be called before execute to
	 * initialize this action.
	 * 
	 * @return void
	 * @access public
	 * @since 6/19/08
	 */
	public function execute ();
}

?>