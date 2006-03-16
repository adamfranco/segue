<?php
/**
 * @since 1/20/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginManager.class.php,v 1.9 2006/03/16 20:08:40 adamfranco Exp $
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
 * @version $Id: PluginManager.class.php,v 1.9 2006/03/16 20:08:40 adamfranco Exp $
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
	
	/**
	 * Constructor
	 * 
	 * @return object PluginManager
	 * @access public
	 * @since 1/20/06
	 */
	function PluginManager () {
		$this->_arrays = array('enabled', 'disabled');

		if (!isset($_SESSION['post_config_setup_complete']))
	 		$this->_registerPlugins(); // not installed, just in filesystem

		if (!isset($_SESSION['enabled_plugins']))
			$this->_enabledPlugins = array();
		else
			$this->_enabledPlugins = $_SESSION['enabled_plugins'];
		
		if (!isset($_SESSION['disabled_plugins']))
			$this->_disabledPlugins = array();
		else
			$this->_disabledPlugins = $_SESSION['disabled_plugins'];

		$this->_objectifyArrays();
	}
	
	/**
	 * Assigns the configuration
	 * 
	 * @param object Configuration $configuration the config set by the system
	 * @return void
	 * @access public
	 * @since 1/24/06
	 */
	function assignConfiguration ( &$configuration ) {
		$this->_configuration =& $configuration;
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
	function assignOsidContext ( &$context ) { 
		$this->_osidContext =& $context;
	} 

	/**
	 * Keeps objects in the plugin manager arrays
	 * 
	 * @return void
	 * @access public
	 * @since 3/9/06
	 */
	function _objectifyArrays () {
		foreach ($this->_arrays as $arrayName) {
			eval('$array = $this->_'.$arrayName.'Plugins;');
			foreach ($array as $key => $keystring) {
				$array[$keystring] =& HarmoniType::fromString($keystring);
				unset($array[$key]);
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
			$cache_array = array();
			eval('$array = $this->_'.$arrayName.'Plugins;');
			foreach (array_keys($array) as $keystring)
				$cache_array[] = $keystring;
			eval('$_SESSION["'.$arrayName.'_plugins"] = $cache_array;');
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
		$this->_registeredPlugins = array();
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
							&& ereg("^[a-zA-Z0-9_]+$", $authDir)) {
						$authority = $authDir;
						
						$aDirHandle = openDir($authPath);
						// finally find all the keyword folders (each is a plugin)
						while ($keyDir = readdir($aDirHandle)) {
							$keyPath = $authPath."/".$keyDir;
							
							if (is_dir($keyPath) && !in_array($keyDir, $ignore)
									&& ereg("^[a-zA-Z0-9_]+$", $keyDir)) {
								$keyword = $keyDir;
								$type = new Type($domain, $authority, $keyword);
								$indexString = $type->printableString();
								// unique types are placed in the array
								if (!isset(
									$this->_registeredPlugins[$indexString]))
									$this->_registeredPlugins[$indexString] = 
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
		return $this->_registeredPlugins;
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
	function &getPlugin ( &$asset ) {
		$type =& $asset->getAssetType();
		$domain = $type->getDomain();
		$authority = $type->getAuthority();
		$keyword = $type->getKeyword();
		
		if ($this->isPluginDomain($domain)) {
			require_once(MYDIR."/main/library/PluginManager/"
				.$domain."/".$domain."Plugin.abstract.php");
			require_once(MYDIR."/main/library/PluginManager/"
				.$domain."/".$domain."AjaxPlugin.abstract.php");
			require_once(MYDIR."/plugins/".$domain."/"
				.$authority."/".$keyword."/"
				.$authority.$keyword."Plugin.class.php");
				
			eval('$plugin =& '.$authority.$keyword.
				'Plugin::newInstance($asset, $this->_configuration);');
			return $plugin;
		}
		
		throwError(new Error("Plugin Manager", "This asset does not contain a 
			plugin"));
	}

	/**
	 * Returns an array of user id strings for currently authenticated users
	 * 
	 * @return string the idstring of the user authenticated under $authNType
	 * @access public
	 * @since 3/6/06
	 */
	function getCurrentUser ($authNType = null) {
		$authN =& Services::getService("AuthN");
		if (is_null($authNType))
			$authNType = $this->_configuration->getProperty('authN_priority');
		$types =& $authN->getAuthenticationTypes();
		$users = array();
		while ($types->hasNext()) {
			$type =& $types->next();
			$userId =& $authN->getUserId($type);
			$users[$type->getKeyword()] = $userId->getIdString();
		}

		return $users[$authNType];
	}

	/**
	 * Answer the XHTML text of the plugin
	 * 
	 * @param object Asset $asset
	 * @param optional boolean $showControls
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getPluginText ( &$asset, $showControls = false ) {
		ob_start();
		$type =& $asset->getAssetType();
		if (in_array($type->printableString(), 
				array_keys(/*$this->_enabledPlugins*/$this->getInstalledPlugins()))) {
			
			$plugin =& $this->getPlugin($asset);
			$plugin->setShowControls($showControls);
			
			$assetId =& $asset->getId();
			
			print SeguePluginsAjaxPlugin::getPluginSystemJavascript();
			
			if (!is_object($plugin)) {
				print $plugin;
			} else {
				$harmoni =& Harmoni::instance();
				$harmoni->request->startNamespace(
					get_class($plugin).':'.$assetId->getIdString());
				$baseUrl =& $harmoni->request->mkURL();
				print $plugin->executeAndGetMarkup($baseUrl);
				$harmoni->request->endNamespace();
			}
		} else {
			print "The requested plugin is not enabled, please contact your administrator for more information";
		}
		return ob_get_clean();
	}
	
	/**
	 * Answer the title Markup for this plugin
	 * 
	 * @param object Asset $asset
	 * @param optional boolean $showControls
	 * @return string
	 * @access public
	 * @since 2/22/06
	 */
	function getPluginTitleMarkup ( &$asset, $showControls = false ) {
		$plugin =& $this->getPlugin($asset);
		$plugin->setShowControls($showControls);
		
		if ($plugin->getPluginTitleMarkup())
			return $plugin->getPluginTitleMarkup();
		else
			return "";
	}

	/**
	 * Installs a plugin
	 * 
	 * @param object HarmoniType $type gives us the location of plugin to be 
	 * installed
	 * @access private
	 * @since 3/6/06
	 */
	function _installPlugin ($type) {
	// @todo deal with new plugin readiness structure, and database tables
		$authZ =& Services::getService("AuthZ");
//		if ($authZ->isUserAuthorized("edu.middlebury.authorization.add_children", ??))	{
			require_once(DOMIT); // for XML DOM
			$dr =& Services::getService("Repository");
			$dm =& Services::getService("DataTypeManager");
			$db =& Services::getService("DBHandler");
			$id =& Services::getService("Id");

			// a few things we need
			$site_rep =& $dr->getRepository(
				$id->getId("edu.middlebury.segue.sites_repository"));
			$pluginDir = $this->getConfiguration('plugin_dir');
			$types = $dm->getRegisteredTypes(); // for partstructures
			
			// use the plugin type to get through the filesystem
			$domain = $type->getDomain();
			$authority = $type->getAuthority();
			$keyword = $type->getKeyword();
			$description = "The type for a $domain $authority $keyword plugin.";

			// write the type to the database
			$query =& new InsertQuery();
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
				$document =& new DOMIT_Document();
				$document->loadXML($xmlFile);
				$recordStructures =& $document->documentElement->childNodes;

				// first create the recordstructure(s)
				foreach ($recordStructures as $rs) {
					if ($rs->hasAttribute("name")) {
						$rsName = $rs->getAttribute("name");
						$plugStruct =& $site_rep->createRecordStructure(
							$rsName,
							"This is the $rsName structure for holding data of the $domain $authority $keyword plugin", "", "");
						$pSId =& $plugStruct->getId();
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
						$query2 =& new SelectQuery();
						$query2->addTable("plugin_type");
						$query2->addColumn("*");
						$query2->addWhere("type_domain = '".addslashes($domain)."'");
						$query2->addWhere("type_authority = '".
							addslashes($authority)."'");
						$query2->addWhere("type_keyword = '".
							addslashes($keyword)."'");
						
						$results =& $db->query($query2, IMPORTER_CONNECTION);
						if ($results->getNumberOfRows() == 1) {
							$result = $results->next();
							$typeId = $result['type_id'];
							$results->free();
							$query3 =& new InsertQuery();
							$query3->setTable("plugin_manager");
							$query3->setColumns(array("FK_plugin_type",
								"FK_schema"));
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
			if (!in_array($type->printableString(), 
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
			
			if (!isset($plugins[$type->printableString()]))
				$plugins[$type->printableString()] = $type;

			eval('$this->_'.$status.'Plugins = $plugins;');
			$this->_cachePluginArrays();
		}
	}

	function _loadPlugins() {
		// cache the installed plugins
		$db =& Services::getService("DBHandler");
		$pm =& Services::getService("Plugs");
		$query = new SelectQuery();
		$query->addTable("plugin_type");
		$query->addColumn("*");
		
		$results =& $db->query($query, IMPORTER_CONNECTION);
		$dis = array();
		$en = array();
		while ($results->hasNext()) {
			$result = $results->next();
			if ($result['type_enabled'] == 0)
				$dis[] = $result['type_domain']."::".
						 $result['type_authority']."::".
						 $result['type_keyword'];
			else
				$en[] =$result['type_domain']."::".
						 $result['type_authority']."::".
						 $result['type_keyword'];
		}
		$this->_disabledPlugins = $dis;
		$this->_enabledPlugins = $en;
		$this->_objectifyArrays();
		$this->_cachePluginArrays();
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