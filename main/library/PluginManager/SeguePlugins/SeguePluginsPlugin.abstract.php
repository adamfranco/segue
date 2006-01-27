<?php
/**
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsPlugin.abstract.php,v 1.5 2006/01/27 16:32:33 adamfranco Exp $
 */ 

require_once (HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

/**
 * Abstract class that all Plugins must extend
 * 
 * @since 1/12/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SeguePluginsPlugin.abstract.php,v 1.5 2006/01/27 16:32:33 adamfranco Exp $
 */
class SeguePluginsPlugin {
 	
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
 	 * as needed.  This is where you would make more complex data that your 
 	 * plugin needs.
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
 	 * Return the markup that represents the plugin.
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
	function url ( $parameters = array() ) {		
		ArgumentValidator::validate($parameters, 
			OptionalRule::getRule(ArrayValidatorRule::getRule()));
		
		$url =& $this->_baseUrl->deepCopy();
		if (is_array($parameters) && count($parameters))
			$url->setValues($parameters);
		return "'".$url->write()."'";
	}
	
	/**
	 * Answer a Javascript command to send the window to a url with the parameters
	 * passed.
	 *
	 * Use this method, e.g.:
	 *		"onclick=".$this->locationSend(array('item' => 123))
	 * instead of the following:
	 * 		"onclick='window.location=\"".$this->url(array('item' => 123))."\"'"
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	function locationSend ( $parameters = array() ) {		
		return "'window.location=\"".$this->url($parameters)."\"'";
	}
	
	/**
	 * Answer a url with the parameters passed, for a form. As well, specify
	 * an optional boolean second parameter, 'isMultipart' if this is a multipart
	 * form with file uploads.
	 *
	 * Use this method, e.g.:
	 *		$this->formTagWithAction(array('item' => 123), false);
	 * instead of the following:
	 * 		"<form action='".$this->url(array('item' => 123))."' method='post>";
	 *
	 * Usage of this method instead of manually writing the form start tag
	 * is optional, but will allow the plugin to more easily be ported to being
	 * an 'AjaxPlugin' later on as the AjaxPlugin redefines the behavior of
	 * this method.
	 * 
	 * @param array $parameters Associative array ('name' => 'value')
	 * @param string $method post OR get
	 * @param boolean $isMultipart
	 * @return string
	 * @access public
	 * @since 1/16/06
	 */
	function formStartTagWithAction ( $parameters = array(), $method = 'post', 
		$isMultipart = false ) 
	{
		// If this is a multipart form, we must do a normal 'submit'
		// that includes a page refresh.
		if ($isMultipart) {
			return "<form action=".$this->url($parameters)." method='post' enctype='multipart/form-data'>";
		} 
		// If the form is not a multipart form with file uploads, then we
		// don't ned the enctype parameter.
		else {
			if (strtolower($method) == 'get')
				$method = 'get';
			else
				$method = 'post';
			return "<form action=".$this->url($parameters)." method='".$method."'>";
		}
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
	 * Answer the persisted 'title' value of this plugin.
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function getTitle () {
		return $this->_asset->getDisplayName();
	}
	
	/**
	 * Set the persisted 'title' value of this plugin.
	 * 
	 * @param string $title
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	function setTitle ( $title ) {
		$this->_asset->updateDisplayName($title);
	}
	
	/**
	 * Answer the persisted 'content' of this plugin.
	 * Content is a single persisted string that can be used if the complexity of
	 * 'dataRecords' is not needed.
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/06
	 */
	function getContent () {
		$content =& $this->_asset->getContent();
		return $content->asString();
	}
	
	/**
	 * Set the persisted 'content' of this plugin.
	 * Content is a single persisted string that can be used if the complexity of
	 * 'dataRecords' is not needed.
	 * 
	 * @param string $content
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	function setContent ( $content ) {
		$string =& Blob::withValue($content);
		$this->_asset->updateContent($string);
	}
	
	/**
	 * Answer the persisted data of this plugin. Changes to this data will be
	 * persisted
	 * 
	 * The data array returned from this function is a 4-dimensional array of 
	 * the following organization:
	 * level 1: associative array using the record name as the 'key' and an 
	 * array of instances of the record as the 'value'
	 * level 2: array of instances of the record, each index (0,1,...) maps to 
	 * an associative array of the fields in the record
	 * level 3: associative array using the field name as the 'key' and an array
	 * of instances of the fields as the 'value'
	 * level 4: array of instances of the field, each index (0,1,...) maps to
	 * the actual value for its instance of this field in this record
	 * Example: to get a value you will need to access the data array with 4
	 * indices; $pluginData['recordName'][0]['fieldName'][3] would return the 
	 * fourth instance of 'fieldName' in the first instance of 'recordName'
	 * NOTE: you can also just access this data array through $this->data
	 *
	 * @return array this is the data array for your plugin
	 * @access public
	 * @since 1/13/06
	 */
	function getDataRecords () {
		return $this->data;
	}

	/**
	 * Automagically updates any changed data in the data array
	 *
	 * @return void
	 * @access public
	 * @since 1/18/06
	 */
	function updateDataRecords () {
		$this->_storeData();
	}
	
	/**
	 * Answer a valid XHTML with any tag or special-character errors fixed.
	 * 
	 * @param string $htmlString
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function cleanHTML ($htmlString) {
		$htmlStringObj =& HtmlString::withValue($htmlString);
 		$htmlStringObj->clean();
 		return $htmlStringObj->asString();
	}
	
	/**
	 * Answer a valid XHTML string trimmed to the specified word length. Cleans
	 * the syntax as well.
	 * 
	 * @param string $htmlString
	 * @param integer $maxWords
	 * @param optional boolean $addElipses Add elipses when trimming.
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function trimHTML ($htmlString, $maxWords, $addElipses = true) {
		$htmlStringObj =& HtmlString::withValue($htmlString);
 		$htmlStringObj->trim($maxWords, $addElipses);
 		return $htmlStringObj->asString();
	}
	
	/**
	 * Answer a string trimmed to the specified word length. Strips html tags
	 * as well
	 * 
	 * @param string $htmlString
	 * @param integer $maxWords
	 * @param optional boolean $addElipses Add elipses when trimming.
	 * @return string
	 * @access public
	 * @since 1/26/06
	 */
	function stripTagsAndTrim ($htmlString, $maxWords, $addElipses = true) {
		$htmlStringObj =& HtmlString::withValue($htmlString);
 		$htmlStringObj->stripTagsAndTrim($maxWords, $addElipses);
 		return $htmlStringObj->asString();
	}

	/**
	 * Answer TRUE if the current user is authorized to modify this plugin instance.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/16/06
	 */
	function canModify () {
		$azManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $azManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.modify"),
				$this->_asset->getId());
	}
	
	/**
	 * Answer TRUE if the current user is authorized to view this plugin instance.
	 * 
	 * @return boolean
	 * @access public
	 * @since 1/16/06
	 */
	function canView () {
		$azManager =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		return $azManager->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"),
				$this->_asset->getId());
	}
	
	/**
	 * Answer the string Id of this plugin
	 * 
	 * @return string
	 * @access public
	 * @since 1/17/06
	 */
	function getId () {
		if (!isset($this->_id)) {
			$id =& $this->_asset->getId();
			$this->_id = $id->getIdString();
		}
		return $this->_id;			
	}

	/**
	 * Answer the filesystem filepath for the plugin
	 * 
	 * @return string the filesystem path to this plugin directory
	 * @access public
	 * @since 1/19/06
	 */
	function getPluginDir () {
		$dir = $this->_configuration->getProperty('plugin_dir')."/";
		$type =& $this->_asset->getAssetType();
		$dir .= $type->getDomain()."/";
		$dir .= $type->getAuthority()."/";
		$dir .= $type->getKeyword()."/";

		return $dir;
	}

	/**
	 * Answer the filesystem filepath for the plugin
	 * 
	 * @return string the filesystem path to this plugin directory
	 * @access public
	 * @since 1/19/06
	 */
	function getPluginPath () {
		$path = $this->_configuration->getProperty('plugin_path')."/";
		$type =& $this->_asset->getAssetType();
		$path .= $type->getDomain()."/";
		$path .= $type->getAuthority()."/";
		$path .= $type->getKeyword()."/";

		return $path;
	}

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
	 */
	function &newInstance ( &$asset, &$configuration ) {
		ArgumentValidator::validate($asset, ExtendsValidatorRule::getRule("Asset"));
		ArgumentValidator::validate($configuration, ExtendsValidatorRule::getRule("Properties"));
		$false = false;
		
		$type =& $asset->getAssetType();
		$pluginDir = $configuration->getProperty("plugin_dir")."/".
			$type->getDomain()."/".$type->getAuthority().
			"/".$type->getKeyword()."/";
		$pluginClass = $type->getAuthority().$type->getKeyword()."Plugin";
		$pluginFile = $pluginDir.$pluginClass.".class.php";
		
		
		// Check for the file
		if (!file_exists($pluginFile))	
			return _("Error: Plugin not found at '$pluginFile'.");
		
		require_once($pluginFile);
		
		
		// Check for the class
		if (!class_exists($pluginClass)) 
			return _("Error: Plugin class, '$pluginClass', not found.");
		
		// Ensure that the plugin writer didn't override the constructor
		if (in_array($pluginClass, get_class_methods($pluginClass)))
			return _("Error: Plugin class should not have a constructor method, '$pluginClass'.");
		
		// Instantiate the plugin
		$plugin =& new $pluginClass;
		
		$plugin->setConfiguration($configuration);
		$plugin->setAsset($asset);
		
		// Execute the decendent's initialization
		$plugin->initialize();
		
		return $plugin;
	}
	
/*********************************************************
 * Object Properties - Non-API
 *********************************************************/
	/**
	 * @var string $_id; The string Id of the Plugin/Asset 
	 * @access private
	 * @since 1/17/06
	 */
	var $_id;
	
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
		
		$type =& $this->_asset->getAssetType();
		$this->_pluginDir = $this->_configuration->getProperty("plugin_dir")."/".$type->getDomain()."/".
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
		
		$this->update($this->_getRequestData());
		
		$markup = $this->getPluginMarkup();
		
		$this->_storeData();
		
		return $markup;
	}
	
	/**
	 * Answer the markup for this plugin
	 * 
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getPluginMarkup () {
		return $this->getMarkup();
	}
	
	/**
	 * Answer the markup for the pluginTitle
	 * 
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getPluginTitleMarkup () {
		return $this->getTitle();
	}
	
	/**
	 * Answer the REQUEST data for this plugin instance.
	 * 
	 * @return array
	 * @access private
	 * @since 1/13/06
	 */
	function _getRequestData () {
		return array();
	}
	
	/**
	 * Load our data from our Asset
	 * 
	 * @return void
	 * @access private
	 * @since 1/12/06
	 */
	function _loadData () {
	// @todo file handling
		// one array for the data, a second for the persistence of ids
		if (isset($this->data))
			unset($this->data, $this->_data_ids);
		$this->data = array();
		$this->_data_ids = array();
		
		// get all the records for this asset
		$records =& $this->_asset->getRecords();
		while ($records->hasNext()) {
			$record =& $records->next();
			
			// for each new recordstructure add an array for holding instances
			$recordStructure =& $record->getRecordStructure();
			$rsName = $recordStructure->getDisplayName();
			if (!in_array($rsName, array_keys($this->data))) {
				$this->data[$rsName] = array();
				$this->_data_ids[$rsName] = array();
			}
			
			// each instance itself should be acessible via index (1,2,3...)
			$this->data[$rsName][] = array();
			$this->_data_ids[$rsName][] = array();
			$instance = count($this->data[$rsName]) - 1; // current instance

			// each instance populates its parts like the records
			$parts =& $record->getParts();
			while ($parts->hasNext()) {
				$part =& $parts->next();

				// for each new partstructure add an array for holding instances
				$partStructure =& $part->getPartStructure();
				$psName = $partStructure->getDisplayName();
// 				if (($rsName == "FILE") && (($psName == "FILE_DATA") 
// 						|| ($psName == "THUMBNAIL_DATA"))) {
// 					// don't touch the data, just the file name (location)
// 				}
// 				else
				if (!in_array($psName, 	
						array_keys($this->data[$rsName][$instance]))) {
					$this->data[$rsName][$instance][$psName] = array();
					$this->_data_ids[$rsName][$instance][$psName] = array();
				}
				
				// again with the instances
				$partValue =& $part->getValue();
				$id =& $part->getId();
				$idString = $id->getIdString();
				$idArray = explode("::", $idString);
				$this->data[$rsName][$instance][$psName][$idArray[2]] = 
					$partValue->asString();
				$this->_data_ids[$rsName][$instance][$psName][$idArray[2]] =&
					$part->getId();
			}
		}
		
		// keep original data for modification check.
		$this->_loadedData = $this->data;
	}
	
	/**
	 * Store and changes to our data set to our Asset
	 * 
	 * @return void
	 * @access public
	 * @since 1/13/06
	 */
	function _storeData () {
		if (isset($changes))
			unset($changes);
		// only change things when you must
		if ($this->_dataChanged()/*@todo lose warnings when no data is here*/) {
			$changes = array();	// array for storing a part id and its new value
			
			// go through all recordstructures
			foreach ($this->data as $rs => $instances) {
				
				if (is_array($instances)) {
					// go through each instance of the recordstructure
					foreach ($instances as $instance => $record) {
						
						if (is_array($record)) {
							// for each array of part values find out which have changed
							foreach ($record as $ps => $values) {
								$differences = array_diff_assoc(
									$values, $this->_loadedData[$rs][$instance][$ps]);
								
								// add each change to the array of changes
								if (count($differences) > 0) {
									foreach ($differences as $key => $value) {
										$changes[$this->_data_ids[$rs][$instance][$ps][$key]->getIdString()] = $value;
									}
								}
							}
						}
					}
				}
			}
		}
		
		// make them changes
		if (isset($changes)) {
		$idManager =& Services::getService("Id");
			foreach ($changes as $idString => $value) {
				$id =& $idManager->getId($idString);
				$part =& $this->_asset->getPart($id);
				$part->updateValueFromString($value);
			}
 		$this->_loadedData = $this->data;
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
		$new = serialize($this->data);
		$old = serialize($this->_loadedData);
		if ($old == $new)
			return false;
		return true;
	}
}
?>