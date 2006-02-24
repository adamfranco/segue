<?php
/**
 * @since 1/20/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginManager.class.php,v 1.5 2006/02/24 20:33:50 cws-midd Exp $
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
 * @version $Id: PluginManager.class.php,v 1.5 2006/02/24 20:33:50 cws-midd Exp $
 */
class PluginManager {
		
	// @todo write the plugin manager, umm, which means clean up the code that 
	// is sitting below here and add more to that so that our plugin system 
	// kicks ass
	
	var $_registeredPlugins;

	var $_plugin;
	
	/**
	 * Constructor
	 * 
	 * @return object PluginManager
	 * @access public
	 * @since 1/20/06
	 */
	function PluginManager () {
 		$this->_registeredPlugins = array();
 		$this->_registerPlugins();
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
	 * Checks if the passed string is a plugin domain
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
	 * Returns an array with currently supported Domains
	 * 
	 * @return array 
	 * @access public
	 * @since 1/20/06
	 */
	function getPluginDomains () {
		$domains = array();
		foreach ($this->_registeredPlugins as $pluginType) {
			if (!in_array($pluginType->getDomain(), $domains))
				$domains[] = $pluginType->getDomain();
		}
		
		return $domains;
	}

	/**
	 * Seeks out defined plugins and puts them in a nice array
	 * 
	 * @access public
	 * @since 2/23/06
	 */
	function _registerPlugins () {
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
				$domain = strtolower($domainDir);
				
				$dDirHandle = openDir($domainPath);
				// now take an authority folder and open it
				while ($authDir = readdir($dDirHandle)) {
					$authPath = $domainPath."/".$authDir;
					
					if (is_dir($authPath) && !in_array($authDir, $ignore)
							&& ereg("^[a-zA-Z0-9_]+$", $authDir)) {
						$authority = strtolower($authDir);
						
						$aDirHandle = openDir($authPath);
						// finally find all the keyword folders (each is a plugin)
						while ($keyDir = readdir($aDirHandle)) {
							$keyPath = $authPath."/".$keyDir;
							
							if (is_dir($keyPath) && !in_array($keyDir, $ignore)
									&& ereg("^[a-zA-Z0-9_]+$", $keyDir)) {
								$keyword = strtolower($keyDir);
								$indexString = $keyword."::".$authority."::".$domain;
								$type = new Type($domain, $authority, $keyword);
								// unique types are placed in the array
								if (!isset($this->_registeredPlugins[$indexString]))
									$this->_registeredPlugins[$indexString] = $type;
							}
						}
					}
				}
			}
		}
	}

	/**
	 * Returns plugins ordered alphabetically by Domain, Authority, or Keyword
	 * 
	 * @param string $orderBy choose from 'domain' 'authority' or 'keyword'
	 * @return array (assoc) containing the type objects for each plugin 
	 * keyed by string representations
	 * @access public
	 * @since 2/23/06
	 */
	function getPluginsBy ($orderBy = "keyword") {
		// @todo take the plugins array and key the type objects on the string 
		// representation of their types with the appropriate piece
		$return = array();
		$three = array("keyword", "authority", "domain");

		foreach ($this->_registeredPlugins as $type) {
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


	eval('$plugin =& '.$authority.$keyword.'Plugin::newInstance($asset, $this->_configuration);');
		}
		
		return $plugin;
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
}

?>