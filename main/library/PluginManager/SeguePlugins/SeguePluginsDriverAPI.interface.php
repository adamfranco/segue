<?php
/**
 * @since 10/25/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsDriverAPI.interface.php,v 1.6 2008/02/18 16:17:43 adamfranco Exp $
 */ 

/**
 * This interface defines the methods that SeguePlugins must implement in order
 * to work with the PluginManager. These methods should be implemented by abstract
 * classes in order to provide functionality for light-weight concrete classes.
 * 
 * @since 10/25/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsDriverAPI.interface.php,v 1.6 2008/02/18 16:17:43 adamfranco Exp $
 */
interface SeguePluginsDriverAPI {
		
/*********************************************************
 *********************************************************
 *********************************************************
 *********************************************************
 * Non-API vars/methods
 * 
 * The variables and methods listed below are not part of the
 * plugin API and should never be called by plugins. Their
 * functionality is used by the plugin system internally
 *
 *********************************************************/

/*********************************************************
 * Class Methods - Instance Creation
 *********************************************************/
	/**
	 * Instantiate a new plugin for an Asset
	 * 
	 * @param object Asset $asset
	 * @param object ConfigurationProperties $configuration
	 * @return object Plugin OR string (error string) on error.
	 * @access public
	 * @since 1/12/06
	 * @static
	 */
	public static function newInstance ( Asset $asset, Properties $configuration );
	
	
/*********************************************************
 * Instance Methods - Non-API
 *********************************************************/
	
	/**
	 * Execute the plugin and return its markup.
	 * 
	 * @param optional boolean $showControls
	 * @param optional boolean $extended	If true, return the extended version. Default: false.
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	public function executeAndGetMarkup ( $showControls = false, $extended = false );
	
	/**
	 * Execute the plugin and return its markup.
	 * 
	 * @param optional boolean $showControls
	 * @param optional boolean $extended
	 * @return string
	 * @access public
	 * @since 5/23/07
	 */
	public function executeAndGetExtendedMarkup ( $showControls = false);
	
	/**
	 * Answer true if this plugin instance has extended content that should
	 * be linked to.
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/23/07
	 */
	public function hasExtendedMarkup ();
	
	/**
	 * Set a custom function for checking if the user can modify the plugin.
	 * This function must accept the plugin as its only argument and return
	 * a boolean. Use the create_function() method to create an anonymous function.
	 *
	 * This may be used to allow the plugin to make use of alternate authorization
	 * systems or settings.
	 * 
	 * @param string $function
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	public function setCanModifyFunction ($function);
	
	/**
	 * Set a custom function for checking if the user can modify the plugin.
	 * This function must accept the plugin as its only argument and return
	 * a boolean. Use the create_function() method to create an anonymous function.
	 *
	 * This may be used to allow the plugin to make use of alternate authorization
	 * systems or settings.
	 * 
	 * @param string $function
	 * @return void
	 * @access public
	 * @since 7/5/07
	 */
	public function setCanViewFunction ($function);
	
	/**
	 * Set what module and action the plugin urls should use. This is needed when
	 * generating markup to be used in another context.
	 * 
	 * @param string $module
	 * @param string $action
	 * @return void
	 * @access public
	 * @since 2/18/08
	 */
	public function setLocalModuleAndAction ($module, $action);
	
/*********************************************************
 * Versioning
 *********************************************************/
	/**
	 * Answer an array of the versions for this plugin instance.
	 *
	 * @return array of SeguePluginVersion objects
	 * @access public
	 * @since 1/7/08
	 */
	public function getVersions ();
	
	/**
	 * Answer a particular version.
	 * 
	 * @param string $versionId
	 * @return object SeguePluginVersion
	 * @access public
	 * @since 1/7/08
	 */
	public function getVersion ($versionId);
	
	/**
	 * Execute the plugin and return the markup for a version.
	 * 
	 * @param object DOMDocument $versionXml
	 * @return string
	 * @access public
	 * @since 1/7/08
	 */
	public function executeAndGetVersionMarkup ( DOMDocument $versionXml );
	
	/**
	 * Import a historical version, for instance from a backup system.
	 * 
	 * @param object DOMDocument $versionXml The version markup.
	 * @param object Id $agentId The agent id that created the version.
	 * @param object DateAndTime $timestamp The time the version was created.
	 * @param string $comment A comment associated with the version.
	 * @return void
	 * @access public
	 * @since 1/23/08
	 */
	public function importVersion (DOMDocument $versionXml, Id $agentId, DateAndTime $timestamp, $comment);
}

/**
 * An exception for Plugins to report invalid version formats.
 * 
 * @since 1/22/08
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsDriverAPI.interface.php,v 1.6 2008/02/18 16:17:43 adamfranco Exp $
 */
class InvalidVersionException
	extends Exception
{
	
}

?>