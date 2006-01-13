<?php
/**
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Plugin.abstract.php,v 1.3 2006/01/13 18:36:32 adamfranco Exp $
 */ 

/**
 * Abstract class that all Plugins must extend
 * 
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Plugin.abstract.php,v 1.3 2006/01/13 18:36:32 adamfranco Exp $
 */
class Plugin {
 	
/*********************************************************
 * Instance Methods - API
 *
 * These are the methods that plugins can and should use 
 * to interact with their environment. 
 * 		Valid additional APIs outside of the methods below:
 *			- OSID interfaces (accessed through Plugin->getManager($managerName))
 *
 * To preserve portability, plugins should not access 
 * other Harmoni APIs, constants, global variables, or
 * the super-globals $_GET, $_POST, $_REQUEST, $_COOKIE.
 *********************************************************/

/*********************************************************
 * Instance Methods - API - Override in Children
 *
 * Override these methods to implement the functionality of
 * a plugin.
 *********************************************************/
 	
 	/**
 	 * Initialize this Plugin. 
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function initialize () {
		// Override as needed.
 	}
 	
 	/**
 	 * Update from environmental ($_REQUEST) data.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function update ( $request ) {
 		// Override as needed.
 	}
 	
 	/**
 	 * Update from environmental ($_REQUEST) data.
 	 * Plugin writers should override this method with their own functionality
 	 * as needed.
 	 * 
 	 * @param array $request
 	 * @return void
 	 * @access public
 	 * @since 1/12/06
 	 */
 	function getMarkup () {
 		return "<p>Override this method to display your pluggin.</p>";
 	}
 	
/*********************************************************
 * Instance Methods - API
 *
 * Use these methods in your plugin as needed, but do not 
 * override them.
 *********************************************************/
	
	/**
	 * Answer a Url string with the array values added as parameters.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function getUrl ( $parameters ) {
		ArgumentValidator::validate($parameters, ArrayValidatorRule::getRule());
		
		$url =& $this->_baseUrl->deepCopy();
		$url->setValues($parameters);
		return $url->write();
	}
	
	/**
	 * Answer the name-spaced field-name for a given name.
	 * This method MUST be used in form inputs for their values to be
	 * accessible to the plugin.
	 * 
	 * @param string $name
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function getFieldName ( $name ) {
		return RequestContext::name($name);
	}
	
	/**
	 * Answer the value of a submitted/requested field (i.e. GET, POST, REQUEST)
	 * 
	 * @param string $name
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function getFieldValue ( $name ) {
		return RequestContext::value($name);
	}
	
	/**
	 * Answer the persisted data of this plugin. Changes to this data will be
	 * persisted
	 * 
	 * @return <##>
	 * @access public
	 * @since 1/13/06
	 */
	function getData () {
		<##>
	}
		
/*********************************************************
 * Class Methods - Instance Creation
 *********************************************************/
	/**
	 * Instantiate a new plugin for an Asset
	 * 
	 * @param object Asset $asset
	 * @param object ConfigurationProperties $configuration
	 * @return object Plugin OR FALSE on error.
	 * @access public
	 * @since 1/12/06
	 */
	function &newInstance ( &$asset, &$configuration ) {
		$false = false;
		
		$type =& $asset->getType();
		$pluginDir = $configuration->getProperty("plugin_dir")."/".
						$type->getAuthority()."/".$type->getKeyword()."/";
		$pluginClass = $type->getAuthority().$type->getKeyword()."Plugin";
		$pluginFile = $pluginDir.$pluginClass.".class.php";
		
		
		// Check for the file
		if (!file_exists($pluginFile))	
			return $false;
		
		require_once($pluginFile);
		
		
		// Check for the class
		if (!class_exists($pluginClass)) 
			return $false;
		
		// Ensure that the plugin writer didn't override the constructor
		if (in_array($pluginClass, get_class_methods($pluginClass)))
			return $false;
		
		// Instantiate the plugin
		$plugin =& new $pluginClass;
		
		$plugin->setConfiguration($configuration);
		$plugin->setAsset($asset);
		
		// Execute the decendent's initialization
		$plugin->initialize();
		
		return $plugin;
	}
	
/*********************************************************
 * Instance Methods
 *********************************************************/
	
	/**
	 * Set the plugin's environmental configuration
	 * 
	 * @param object ConfigurationProperties $configuration
	 * @return void
	 * @access public
	 * @since 1/12/06
	 */
	function setConfiguration ( &$configuration ) {
		if (isset($this->_configuration))
			throwError(new Error("Configuration already set.", "Plugin.abstract", true));
			
		$this->_configuration =& $configuration;
	}
	
	/**
	 * Inialize ourselves with our data-source asset
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 1/12/06
	 */
	function setAsset ( &$asset ) {
		if (isset($this->_asset))
			throwError(new Error("Asset already set.", "Plugin.abstract", true));
		
		$this->_asset =& $asset;
		
		$type =& $this->_asset->getType();
		$this->_pluginDir = $configuration->getProperty("plugin_dir")."/".
						$type->getAuthority()."/".$type->getKeyword()."/";
		
		$this->_loadData();
	}
	
	/**
	 * Execute the plugin and return its markup.
	 * 
	 * @param object URLWriter $baseUrl
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function executeAndGetMarkup ($baseUrl) {
		ArgumentValidator::validate($baseUrl, ExtendsValidatorRule::getRule('URLWriter'));
		
		$this->_baseUrl =& $baseUrl;
		$this->update();
		$markup = $this->getMarkup();
		$this->_storeData();
		return $markup;
	}
	
	/**
	 * Load our data from our Asset
	 * 
	 * @return void
	 * @access private
	 * @since 1/12/06
	 */
	function _loadData () {
		// @todo Pull data from asset and put into wrapped/easy format
	}
	
	/**
	 * Store and changes to our data set to our Asset
	 * 
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	function _storeData () {
		if ($this->_dataChanged()) {
			// @todo Put wrapped/easy format changes into Asset Records
		}
	}
	
	/**
	 * Answer true if our data has been modified
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/13/06
	 */
	function _dataChanged () {
		// @todo implement
	}
}

?>