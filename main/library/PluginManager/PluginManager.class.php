<?php
/**
 * @since 1/20/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginManager.class.php,v 1.34 2008/03/21 19:17:31 adamfranco Exp $
 */ 

/**
 * The plugin manager is what allows our plugin system to work seamlessly with 
 * plugins from different CT/LMS systems.
 * 
 * It finds and executes the appropriate setup and functions to support plugins
 * written for other systems and under other specifications.
 * 
 * @since 1/20/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginManager.class.php,v 1.34 2008/03/21 19:17:31 adamfranco Exp $
 */
class PluginManager {
		
	// @todo write the plugin manager, umm, which means clean up the code that 
	// is sitting below here and add more to that so that our plugin system 
	// kicks ass
	
	var $_registeredPlugins;
	var $_enabledPlugins;
	var $_disabledPlugins;
	var $_arrays;

	var $_plugin;
	var $_plugins = array();
	
	/**
	 * Constructor
	 * 
	 * @return object PluginManager
	 * @access public
	 * @since 1/20/06
	 */
	function PluginManager () {
		$this->_arrays = array('enabled', 'disabled');
		
		$this->_pluginClasses = array();
		$this->_pluginDirs = array();

		if (!isset($_SESSION['registered_plugins']))
	 		$this->_registerPlugins(); // not installed, just in filesystem

		if (!isset($_SESSION['enabled_plugins']))
			$this->_enabledPlugins = array();
		else
			$this->_enabledPlugins = $_SESSION['enabled_plugins'];
		
		if (!isset($_SESSION['disabled_plugins']))
			$this->_disabledPlugins = array();
		else
			$this->_disabledPlugins = $_SESSION['disabled_plugins'];
		
		// @todo Remove this in Beta 12
		$this->convertBeta10KeysTo11();
	}
	
	/**
	 * Keeps objects in the plugin manager arrays.
	 *
	 * This should be removed in beta 12. it is being kept around just to update
	 * existing sessions during the version change-over period from beta 9-10 to beta 11.
	 *
	 * @todo Remove this in Beta 12
	 * @return void
	 * @access private
	 * @since 3/9/06
	 */
	private function convertBeta10KeysTo11 () {
		foreach ($this->_arrays as $arrayName) {
			eval('$array = $this->_'.$arrayName.'Plugins;');
			foreach ($array as $key => $keystringOrObj) {
				if ($keystringOrObj && is_string($keystringOrObj)) {
					$array[$keystringOrObj] = HarmoniType::fromString($keystringOrObj);					
					unset($array[$key]);
				}				
			}
			eval('$this->_'.$arrayName.'Plugins = $array;');
		}
		
		$this->_addTypeDescriptions();
		$this->_cachePluginArrays();
	}
	
	/**
	 * Assigns the configuration
	 * 
	 * @param object Configuration $configuration the config set by the system
	 * @return void
	 * @access public
	 * @since 1/24/06
	 */
	function assignConfiguration ( $configuration ) {
		$this->_configuration = $configuration;
	}

	/**
	 * returns a configuration value
	 * 
	 * @param string $key the key for the configuration
	 * @return mixed the config for the given key
	 * @access public
	 * @since 3/3/06
	 */
	function getConfiguration ($key) {
		return $this->_configuration->getProperty($key);
	}

	/**
	 * Assign the context of this OsidManager.
	 * 
	 * @param object OsidContext $context
	 * 
	 * @throws object OsidException An exception with one of the following
	 *		   messages defined in org.osid.OsidException:	{@link
	 *		   org.osid.OsidException#NULL_ARGUMENT NULL_ARGUMENT}
	 * 
	 * @access public
	 */
	function assignOsidContext ( $context ) { 
		$this->_osidContext = $context;
	}
	
	
	
	/**
	 * Add descriptions to the type array
	 * 
	 * @return void
	 * @access public
	 * @since 6/1/07
	 */
	function _addTypeDescriptions () {
		foreach ($this->_arrays as $arrayName) {
			eval('$array = $this->_'.$arrayName.'Plugins;');
			foreach ($array as $key => $type) {				
				$this->_loadPluginFiles($type);
				$class = $this->getPluginClass($type);
				
				$description = call_user_func(array($class, 'getPluginDescription'));
				$array[$key] = new Type(
					$type->getDomain(),
					$type->getAuthority(),
					$type->getKeyword(),
					$description);
			}
			eval('$this->_'.$arrayName.'Plugins = $array;');
		}
	}

	/**
	 * caches the plugin arrays
	 * 
	 * @return void
	 * @access public
	 * @since 3/9/06
	 */
	function _cachePluginArrays () {
		foreach ($this->_arrays as $arrayName) {
			eval('$_SESSION["'.$arrayName.'_plugins"] = $this->_'.$arrayName.'Plugins;');
		}
	}
	
	/**
	 * Include the class files for a plugin
	 * 
	 * @param object Type $type
	 * @return void
	 * @access public
	 * @since 6/1/07
	 */
	function _loadPluginFiles ($type) {
		// Clean type components to safe strings.
		$domain = $type->getDomain();
		$authority = $type->getAuthority();
		$keyword = $type->getKeyword();
		
		if (preg_match('/[^a-z0-9_\-\s]/i', $domain))
			throw new Exception("Invalid plugin domain, '".$domain."'.");
		
		if (preg_match('/([^a-z0-9_\-\s\.]|\.{2,})/i', $authority))
			throw new Exception("Invalid plugin authority, '".$authority."'.");
			
		if (preg_match('/[^a-z0-9_\-]/i', $keyword))
			throw new Exception("Invalid plugin keyword, '".$keyword."'.");
		
		
		$pluginClassFile = $this->getPluginDir($type)
			.$this->getPluginClass($type).".class.php";
		
		if ($this->isPluginDomain($domain)) {
			if (!file_exists($pluginClassFile))
				throw new Exception("Missing Plugin class file '$pluginClassFile'.");
				
			require_once(MYDIR."/main/library/PluginManager/"
				.$domain."/include.php");
			require_once($pluginClassFile);
			

		} else {
			$plugins = $this->getRegisteredPlugins();
			// Check to see if this plugin even exists
			foreach ($plugins as $plugType) {
				if ($type->getDomain() == $plugType->getDomain())
					throwError(new Error("This asset does not contain a plugin. Domain, '".$domain."' exists, but is not installed.", "Plugin Manager"));
			}
			// Otherwise give a generic error.
			throwError(new Error("This asset does not contain a 
				plugin. Type, '".$type->asString()."' does not match any plugins in the registered plugins: ".printpre($this->getRegisteredPlugins(), true), "Plugin Manager"));
		}
	}

	/**
	 * Checks if the passed string is a plugin domain
	 * This is used to determine which nodes are plugins
	 * 
	 * @param string $domain asset type domain for checking
	 * @return boolean
	 * @access public
	 * @since 1/24/06
	 */
	function isPluginDomain ($domain) {
		return in_array(strtolower($domain), $this->getPluginDomains());
	}

	/**
	 * Returns an array with currently installed plugin Domains
	 * 
	 * @return array 
	 * @access public
	 * @since 1/20/06
	 */
	function getPluginDomains () {
		$domains = array();
		foreach ($this->getInstalledPlugins() as $pluginType) {
			if (!in_array(strtolower($pluginType->getDomain()), $domains))
				$domains[] = strtolower($pluginType->getDomain());
		}
		
		return $domains;
	}

	/**
	 * Seeks out plugins in the filesystem and puts them in a nice array
	 * These plugins are not necessarily installed
	 * 
	 * @access public
	 * @since 2/23/06
	 */
	function _registerPlugins () {
		$_SESSION['registeredPlugins'] = array();
		// open the plugin directory
		$plugPath = MYDIR."/plugins/";
		$pDirHandle = openDir($plugPath);
		// directories that should be there and are not plugins
		$ignore = array(".", "..", "CVS");
				
		// first grab a domain folder and then look inside it
		while ($domainDir = readdir($pDirHandle)) {
			$domainPath = $plugPath."/".$domainDir;
			
			if (is_dir($domainPath) && !in_array($domainDir, $ignore)
					&& ereg("^[a-zA-Z0-9_]+$", $domainDir)) {
				$domain = $domainDir;
				
				$dDirHandle = openDir($domainPath);
				// now take an authority folder and open it
				while ($authDir = readdir($dDirHandle)) {
					$authPath = $domainPath."/".$authDir;
					
					if (is_dir($authPath) && !in_array($authDir, $ignore)
							&& ereg("^[a-zA-Z0-9\._]+$", $authDir)) {
						$authority = $authDir;
						
						$aDirHandle = openDir($authPath);
						// finally find all the keyword folders (each is a plugin)
						while ($keyDir = readdir($aDirHandle)) {
							$keyPath = $authPath."/".$keyDir;
							
							if (is_dir($keyPath) && !in_array($keyDir, $ignore)
									&& ereg("^[a-zA-Z0-9_]+$", $keyDir)) {
								$keyword = $keyDir;
																
								$type = new Type($domain, $authority, $keyword);
								$indexString = $type->asString();
								// unique types are placed in the array
								if (!isset(
									$_SESSION['registeredPlugins'][$indexString]))
									$_SESSION['registeredPlugins'][$indexString] = 
										$type;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * returns the registered plugins
	 * 
	 * @return array 
	 * @access public
	 * @since 3/9/06
	 */
	function getRegisteredPlugins () {
		if (!isset($_SESSION['registeredPlugins']))
			$this->_registerPlugins();
		return $_SESSION['registeredPlugins'];
	}

	/**
	 * Returns plugins ordered alphabetically by Domain, Authority, or Keyword
	 * Use the status parameter for choosing to see only enabled, disabled, 
	 * installed, or registered.
	 *
	 * @param string $orderBy choose from 'domain' 'authority' or 'keyword'
	 * @param string $status choose from 'registered' 'installed' 'enabled' or 'disabled'
	 * @return array (assoc) containing the type objects for each plugin 
	 * keyed by string representations
	 * @access public
	 * @since 2/23/06
	 */
	function getPluginsBy ($orderBy = "keyword", $status = 'enabled') {
		$return = array();
		$three = array("keyword", "authority", "domain");
		$plugins = array();
		
		if (in_array($status, $this->_arrays))
			eval('$plugins = $this->_'.$status.'Plugins;');

		foreach ($plugins as $type) {
			eval('$key = $type->get'.ucfirst(strtolower($orderBy)).'();');
			
			foreach ($three as $dak) {
				if ($dak != strtolower($orderBy))
					eval('$key .= "::".$type->get'.ucfirst($dak).'();');
			}
			
			$return[$key] = $type;
		}
		ksort($return);
		return $return;
	}

	/**
	 * Returns a new instance of the plugin class for the domain of the asset
	 * 
	 * @param object Asset $asset is the asset for which we want the plugin
	 * @return object Plugin
	 * @access public
	 * @since 1/24/06
	 */
	function getPlugin ( $asset ) {
		$id = $asset->getId();
		$idstring = $id->getIdString();
		if (!isset($this->_plugins[$idstring])) {
			$type = $asset->getAssetType();
			$this->_loadPluginFiles($type);
				
			eval('$this->_plugins[$idstring] = '.$this->getPluginClass($type).
				'::newInstance($asset, $this->_configuration);');
		}
		if (!isset($this->_plugins[$idstring]) || !$this->_plugins[$idstring])
			throwError(new Error("Plugin (id = '$idstring', type = '".$type->asString()."') retrieval failed.", "Segue.Plugins"));
		
		return $this->_plugins[$idstring];
	}
	
	/**
	 * Answer the Plugin class for a given type
	 * 
	 * @param object Type $type
	 * @return string
	 * @access public
	 * @since 1/12/07
	 */
	function getPluginClass ( $type ) {
		if (!isset($this->_pluginClasses[$type->asString()])) {
			// Clean type components to safe strings.
			$domain = $type->getDomain();
			$authority = $type->getAuthority();
			$keyword = $type->getKeyword();
			
			if (preg_match('/[^a-z0-9_\-\s]/i', $domain))
				throw new Exception("Invalid plugin domain, '".$domain."'.");
			
			if (preg_match('/([^a-z0-9_\-\s\.]|\.{2,})/i', $authority))
				throw new Exception("Invalid plugin authority, '".$authority."'.");
				
			if (preg_match('/[^a-z0-9_\-]/i', $keyword))
				throw new Exception("Invalid plugin keyword, '".$keyword."'.");
			
			// Convert an authority like 'edu.middlebury' to 'EduMiddlebury'
			// for use in classnames.
			$authorityParts = explode('.', $authority);
			$authorityClassPart = '';
			foreach ($authorityParts as $part)
				$authorityClassPart .= ucFirst($part);
			
			$this->_pluginClasses[$type->asString()] = $authorityClassPart.$keyword."Plugin";
		}
		
		return $this->_pluginClasses[$type->asString()];
	}
	
	/**
	 * Answer the Plugin class for a given type
	 * 
	 * @param object Type $type
	 * @return string
	 * @access public
	 * @since 1/12/07
	 */
	function getPluginDir ( $type ) {
		if (!isset($this->_pluginDirs[$type->asString()])) {
			// Clean type components to safe strings.
			$domain = $type->getDomain();
			$authority = $type->getAuthority();
			$keyword = $type->getKeyword();
			
			if (preg_match('/[^a-z0-9_\-\s]/i', $domain))
				throw new Exception("Invalid plugin domain, '".$domain."'.");
			
			if (preg_match('/([^a-z0-9_\-\s\.]|\.{2,})/i', $authority))
				throw new Exception("Invalid plugin authority, '".$authority."'.");
				
			if (preg_match('/[^a-z0-9_\-]/i', $keyword))
				throw new Exception("Invalid plugin keyword, '".$keyword."'.");
			
			$this->_pluginDirs[$type->asString()] = MYDIR."/plugins/".$domain."/"
						.$authority."/".$keyword."/";
		}
		
		return $this->_pluginDirs[$type->asString()];
	}
	
	/**
	 * Answer the url of an icon image for a given plugin type
	 * 
	 * @param object Type $type
	 * @return string
	 * @access public
	 * @since 6/4/07
	 */
	function getPluginIconUrl ( $type ) {
		$icon = $this->getPluginDir($type)."/icon.png";
		if (file_exists($icon)) {
			$harmoni = Harmoni::instance();
			$harmoni->request->startNamespace('plugin_manager');
			$url = $harmoni->request->quickURL('plugin_manager', 'icon',
					array('type' => $type->asString()));
			$harmoni->request->endNamespace();
			return $url;
		} else {
			return null;
		}
	}

	/**
	 * Returns an array of user id strings for currently authenticated users
	 * 
	 * @return string the idstring of the user authenticated under $authNType
	 * @access public
	 * @since 3/6/06
	 */
	function getCurrentUser ($authNType = null) {
		$authN = Services::getService("AuthN");
		if (is_null($authNType))
			$authNType = $this->_configuration->getProperty('authN_priority');
		$types = $authN->getAuthenticationTypes();
		$users = array();
		while ($types->hasNext()) {
			$type = $types->next();
			$userId = $authN->getUserId($type);
			$users[$type->getKeyword()] = $userId->getIdString();
		}

		return $users[$authNType];
	}

	/**
	 * Answer the XHTML text of the plugin
	 * 
	 * @param object Asset $asset
	 * @param optional boolean $showControls
	 * @param optional boolean $extended
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getPluginMarkup ( $asset, $showControls = false, $extended = false ) {
		ob_start();
		$type = $asset->getAssetType();
		if (in_array($type->asString(), 
				array_keys(/*$this->_enabledPlugins*/$this->getInstalledPlugins()))) {
			
			$plugin = $this->getPlugin($asset);
						
			if (!is_object($plugin)) {
				print $plugin;
			} else {
				print $plugin->executeAndGetMarkup($showControls, $extended);
			}
		} else {
			print "The requested plugin, '".$type->asString()."' is not enabled, please contact your administrator for more information";
		}
		return ob_get_clean();
	}
	
	/**
	 * Answer the extended version of the XHTML text of the plugin
	 * 
	 * @param object Asset $asset
	 * @param optional boolean $showControls
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getExtendedPluginMarkup ( $asset, $showControls = false) {
		return $this->getPluginMarkup($asset, $showControls, true);
	}
	
	/**
	 * Answer the description plain or HTML text for this plugin
	 * 
	 * @param object Asset $asset
	 * @return string
	 * @access public
	 * @since 2/22/06
	 */
	function getPluginDescription ( $asset ) {
		$plugin = $this->getPlugin($asset);
		
		if ($plugin->getDescription())
			return $plugin->getDescription();
		else
			return "";
	}
	
	/**
	 * Answer true if the plugin type is enabled
	 * 
	 * @param object Type $type
	 * @return boolean
	 * @access public
	 * @since 12/18/07
	 */
	public function isEnabled (Type $type) {
		foreach ($this->_enabledPlugins as $pluginType) {
			if ($pluginType->isEqual($type))
				return true;
		}
		return false;
	}
	
	/**
	 * Answer true if the plugin type is installed
	 * 
	 * @param object Type $type
	 * @return boolean
	 * @access public
	 * @since 12/18/07
	 */
	public function isInstalled (Type $type) {
		foreach ($this->getInstalledPlugins() as $pluginType) {
			if ($pluginType->isEqual($type))
				return true;
		}
		return false;
	}

	/**
	 * Installs a plugin
	 * 
	 * @param object HarmoniType $type gives us the location of plugin to be 
	 * installed
	 * @access public
	 * @since 3/6/06
	 */
	function installPlugin ($type) {
	// @todo deal with new plugin readiness structure, and database tables
		$authZ = Services::getService("AuthZ");
//		if ($authZ->isUserAuthorized("edu.middlebury.authorization.add_children", ??))	{
			$dr = Services::getService("Repository");
			$dm = Services::getService("DataTypeManager");
			$db = Services::getService("DBHandler");
			$id = Services::getService("Id");

			// a few things we need
			$site_rep = $dr->getRepository(
				$id->getId("edu.middlebury.segue.sites_repository"));
			$pluginDir = $this->getConfiguration('plugin_dir');
			$types = $dm->getRegisteredTypes(); // for partstructures
			
			// use the plugin type to get through the filesystem
			$domain = $type->getDomain();
			$authority = $type->getAuthority();
			$keyword = $type->getKeyword();
			$description = "The type for a $domain $authority $keyword plugin.";

			// write the type to the database
			$query = new InsertQuery();
			$query->setTable('plugin_type');
			$query->setColumns( array("type_domain", "type_authority", "type_keyword", "type_description", "type_enabled"));
			$query->addRowOfValues( array("'".addslashes($domain)."'", 
				"'".addslashes($authority)."'", "'".addslashes($keyword)."'", 
				"'".addslashes($description)."'", '0'));
			$db->query($query, IMPORTER_CONNECTION);

			// grab the xml file
			$xmlFile =  $pluginDir."/".$domain."/".$authority."/".$keyword.
				"/".$authority.$keyword."Plugin.xml";

			// if there is no file then the plugin has no data structures
			if (is_file($xmlFile)) {
				$document = new DOMDocument();
				$document->loadXML($xmlFile);
				$recordStructures = $document->documentElement->childNodes;

				// first create the recordstructure(s)
				foreach ($recordStructures as $rs) {
					if ($rs->hasAttribute("name")) {
						$rsName = $rs->getAttribute("name");
						$plugStruct = $site_rep->createRecordStructure(
							$rsName,
							"This is the $rsName structure for holding data of the $domain $authority $keyword plugin", "", "");
						$pSId = $plugStruct->getId();
						$partStructures = $rs->childNodes;
						// now create the partstructure(s)
						foreach ($partStructures as $ps) {
							if ($ps->hasAttribute("name") && 
									$ps->hasAttribute("type")) {
								$psName = $ps->getAttribute("name"); 
								$psType = $ps->getAttribute("type");
								if (in_array($psType, $types))
									$plugStruct->createPartStructure(
										$psName, 
										"This is the $psName structure for holding data of the $domain $authority $keyword plugin",
										new Type ("Repository",
											"edu.middlebury.segue",
											$psType),
										false, true, false);
							}
						}
						// write to the DB the plugin and its structures
						$typeId = null;
						$query2 = new SelectQuery();
						$query2->addTable("plugin_type");
						$query2->addColumn("*");
						$query2->addWhere("type_domain = '".addslashes($domain)."'");
						$query2->addWhere("type_authority = '".
							addslashes($authority)."'");
						$query2->addWhere("type_keyword = '".
							addslashes($keyword)."'");
						
						$results = $db->query($query2, IMPORTER_CONNECTION);
						if ($results->getNumberOfRows() == 1) {
							$result = $results->next();
							$typeId = $result['type_id'];
							$results->free();
							$query3 = new InsertQuery();
							$query3->setTable("plugin_manager");
							$query3->setColumns(array("fk_plugin_type",
								"fk_schema"));
							$query3->addRowOfValues(array (
								"'".addslashes($typeId)."'",
								"'".addslashes($pSId->getIdString())."'"));
							
							$db->query($query3, IMPORTER_CONNECTION);
						} else {
							$results->free();
							throwError(new Error("PluginType not found", 
								"Plugins", false));
						}
					}
				}
			}
			if (!in_array($type->asString(), 
					array_keys($this->getInstalledPlugins())))
				$this->addPluginToArray($type);
	//	}
	}
	
	/**
	 * Adds a plugin to one of the plugin arrays
	 * 
	 * @param object HarmoniType $type the type (plugin) to be added
	 * @param string $status the array that the plugin should be added to
	 * @return void
	 * @access public
	 * @since 3/7/06
	 */
	function addPluginToArray ($type, $status = 'disabled') {
		if (in_array($status, $this->_arrays)) {
			eval('$plugins = $this->_'.$status.'Plugins;');
			
			if (!isset($plugins[$type->asString()]))
				$plugins[$type->asString()] = $type;

			eval('$this->_'.$status.'Plugins = $plugins;');
			$this->_cachePluginArrays();
		}
	}
	
	/**
	 * Enable a plugin
	 * 
	 * @param object Type $type
	 * @return void
	 * @access public
	 * @since 8/22/07
	 */
	public function enablePlugin (Type $type) {
		$db = Services::getService("DBHandler");
	// write the type to the database
		$query = new UpdateQuery();
		$query->setTable('plugin_type');
		$query->addValue("type_enabled", '1');
		$query->addWhereEqual("type_domain", $type->getDomain());
		$query->addWhereEqual("type_Authority", $type->getAuthority());
		$query->addWhereEqual("type_keyword", $type->getKeyword());
		
		$db->query($query, IMPORTER_CONNECTION);
		
		$this->addPluginToArray($type, 'enabled');
	}

	function _loadPlugins() {
		// cache the installed plugins
		$db = Services::getService("DBHandler");
		$pm = Services::getService("Plugs");
		$query = new SelectQuery();
		$query->addTable("plugin_type");
		$query->addColumn("*");
		$query->addOrderBy('type_id');
				
		$results = $db->query($query, IMPORTER_CONNECTION);
		$dis = array();
		$en = array();
		while ($results->hasNext()) {
			$result = $results->next();
			$pluginType = new Type($result['type_domain'],
						 $result['type_authority'],
						 $result['type_keyword']);
			
			$class = $this->getPluginClass($pluginType);
			if (class_exists($class)) {
				$pluginType = new Type(
					$pluginType->getDomain(),
					$pluginType->getAuthority(),
					$pluginType->getKeyword(),
					call_user_func(array($class, 'getPluginDescription')));
			}
			
			if ($result['type_enabled'] == 1)
				$this->_enabledPlugins[HarmoniType::typeToString($pluginType)] = $pluginType;
			else
				$this->_disabledPlugins[HarmoniType::typeToString($pluginType)] = $pluginType;
		}
		$this->_cachePluginArrays();
	}
	
	/**
	 * Returns the array containing all the enabled plugins
	 * 
	 * @return array
	 * @access public
	 * @since 1/11/07
	 */
	function getEnabledPlugins () {
		return $this->_enabledPlugins;
	}
	
	/**
	 * Returns the array containing all the installed plugins
	 * 
	 * @return array
	 * @access public
	 * @since 3/7/06
	 */
	function getInstalledPlugins () {
		return array_merge($this->_enabledPlugins, $this->_disabledPlugins);
	}
}

?>