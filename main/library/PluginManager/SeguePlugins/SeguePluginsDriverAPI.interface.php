<?php
/**
 * @since 10/25/07
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsDriverAPI.interface.php,v 1.1 2007/10/25 20:27:00 adamfranco Exp $
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
 * @version $Id: SeguePluginsDriverAPI.interface.php,v 1.1 2007/10/25 20:27:00 adamfranco Exp $
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
	public static function newInstance ( $asset, $configuration );
	
	
/*********************************************************
 * Instance Methods - Non-API
 *********************************************************/
	
	/**
	 * Set the plugin's environmental configuration
	 * 
	 * @param object ConfigurationProperties $configuration
	 * @return void
	 * @access public
	 * @since 1/12/06
	 */
	public function setConfiguration ( $configuration );
	
	/**
	 * Inialize ourselves with our data-source asset
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/12/06
	 */
	public function setAsset ( $asset );
	
	/**
	 * Set the status of showControls.
	 * 
	 * @param boolean $showControls
	 * @return void
	 * @access public
	 * @since 2/22/06
	 */
	public function setShowControls ($showControls);
	
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
	
}

?>