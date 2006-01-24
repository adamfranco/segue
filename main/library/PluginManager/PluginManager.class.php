<?php
/**
 * @since 1/20/06
 * @package segue.plugin_manager
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginManager.class.php,v 1.1 2006/01/24 20:04:35 cws-midd Exp $
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
 * @version $Id: PluginManager.class.php,v 1.1 2006/01/24 20:04:35 cws-midd Exp $
 */
class PluginManager {
		
	// @todo write the plugin manager, umm, which means clean up the code that 
	// is sitting below here and add more to that so that our plugin system 
	// kicks ass
	
	var $_pluginDomains;

	var $_plugin;
	
	/**
	 * Constructor
	 * 
	 * @return object PluginManager
	 * @access public
	 * @since 1/20/06
	 */
	function PluginManager () {
		$this->_pluginDomains = array();
		$this->findPluginDomains();
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
		return in_array($domain, $this->_pluginDomains);
	}

	/**
	 * Populates the _registeredDomains array with currently supported Domains
	 * 
	 * @return void
	 * @access public
	 * @since 1/20/06
	 */
	function findPluginDomains () {
		$dir = openDir(MYDIR."/main/library/PluginManager");
		
		while ($file = readdir($dir)) {
			if (is_dir($file) && $file != "." && $file != ".." 
					&& $file != "CVS")
				$this->_pluginDomains[] = $file;
		}
	}

	/**
	 * Returns a new instance of the plugin class for the domain of the asset
	 * 
	 * @param object Asset $asset is the asset for which we want the plugin
	 * @return object Plugin
	 * @access public
	 * @since 1/24/06
	 */
	function getPlugin ( &$asset ) {
		$type =& $asset->getAssetType();
		$domain = $type->getDomain();
		
		if (in_array($domain, $this->_pluginDomains)) {
			$plugin = eval($domain."Plugin::newInstance($asset, $this->_configuration)");
		}
		
		return $plugin;
	}

	/**
	 * Answer the XHTML text of the plugin
	 * 
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getPluginText ( &$asset ) {
		ob_start();
		$plugin =& $this->getPlugin($asset);
		
		$assetId =& $asset->getId();
		
		print AjaxPlugin::getPluginSystemJavascript();
		
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
}

?>