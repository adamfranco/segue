<?php
/**
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Plugin.abstract.php,v 1.6 2006/01/13 21:10:17 cws-midd Exp $
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
 * @version $Id: Plugin.abstract.php,v 1.6 2006/01/13 21:10:17 cws-midd Exp $
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
		// @todo implement this
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
		
		// one array for the data, a second for the persistence of ids
		$this->_data = array();
		$this->_data_ids = array();
		
		// get all the records for this asset
		$records =& $this->_asset->getRecords();
		while ($records->hasNext()) {
			$record =& $records->next();
			
			// for each new recordstructure add an array for holding instances
			$recordStructure =& $record->getRecordStructure();
			$rsName = $recordStructure->getDisplayName();
			if (!in_array($rsName, array_keys($this->_data))) {
				$this->_data[$rsName] = array();
				$this->_data_ids[$rsName] = array();
			}
			
			// each instance itself should be acessible via index (1,2,3...)
			$this->_data[$rsName][] = array();
			$this->_data_ids[$rsName][] = $record->getId();
			$instance = count($this->_data[$rsName]) - 1; // current instance

			// each instance populates its parts like the records
			$parts =& $record->getParts();
			while ($parts->hasNext()) {
				$part =& $parts->next();
				
				// for each new partstructure add an array for holding instances
				$partStructure =& $part->getPartStructure();
				$psName = $partStructure->getDisplayName();
				if (!in_array($psName, 	
						array_keys($this->_data[$rsName][$instance]))) {
					$this->_data[$rsName][$instance][$psName] = array();
					$this->_data_ids[$rsName][$instance][$psName] = array();
				}
				
				// again with the instances
				$this->_data[$rsName][$instance][$psName][] =& 
					$part->getValue();
				$this->_data_ids[$rsName][$instance][$psName][] =&
					$part->getId();
			}
		}
		
		// possible modification check.
		$this->_loadedData = $this->_data;
	}
	
	/**
	 * Store and changes to our data set to our Asset
	 * 
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	function _storeData () {
	
		// only change things when you must
		if ($this->_dataChanged()) {
			$changes = array();	// array for storing a part id and its new value
			
			// go through all recordstructures
			foreach ($this->_data as $rs => $instances) {
				
				// go through each instance of the recordstructure
				foreach ($instances as $instance => $record) {
				
					// for each array of part values find out which have changed
					foreach ($record as $ps => $values) {
						$differences = array_diff_assoc(
							$values, $this->_loadedData[$rs][$instance][$ps]);
						
						// add each change to the array of changes
						if (count($differences) > 0) {
							foreach ($differences as $key => $value) {
								$changes[$this->_data_ids[$rs][$instance][$ps][$key]] = $value;
							}
						}
					}
				}
			}
		}
		
		// make them changes
		foreach ($changes as $id => $value) {
			$part =& $this->_asset->getPart($id);
			$part->updateValue($value);
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
		// @todo test different implementations of this function
		$new = serialize($this->_data);
		$old = serialize($this->_loadedData);
		if ($old == $new)
			return false;
		return true;
	}
}

?>