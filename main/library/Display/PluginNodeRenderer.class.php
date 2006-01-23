<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginNodeRenderer.class.php,v 1.8 2006/01/23 15:58:58 adamfranco Exp $
 */ 

/**
 * The NodeRenderer class takes an Asset and renders its navegational item,
 * as well as its children if selected
 * 
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginNodeRenderer.class.php,v 1.8 2006/01/23 15:58:58 adamfranco Exp $
 */
class PluginNodeRenderer
	extends NodeRenderer
{
	/**
	 * @var object Plugin $_plugin;  
	 * @access private
	 * @since 1/20/06
	 */
	var $_plugin;
	
	/**
	 * Answer the GUI component for the navegational item.
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderNavComponent ($level = 1) {
		if ($this->getTitle())
			$title = "\n<div style='font-size: larger; font-weight: bold; border-bottom: 1px solid; margin-bottom: 5px;'>".$this->getTitle()."</div>";
		else
			$title = "";
		$component =& new MenuItem(
						$title.$this->getPluginText(),
						$level);
						
		return $component;
	}
	
	/**
	 * Answer the GUI component for target area
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderTargetComponent ($level = 1) {
		$component =& new Block($this->getPluginText(), STANDARD_BLOCK);
		return $component;
	}
	
	/**
	 * Answer the XHTML text of the plugin
	 * 
	 * @return string
	 * @access public
	 * @since 1/20/06
	 */
	function getPluginText () {
		ob_start();
		$plugin =& $this->getPlugin();
		
		$assetId =& $this->_asset->getId();
		
		
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
	
	/**
	 * Answer the plugin
	 * 
	 * @return object Plugin
	 * @access public
	 * @since 1/20/06
	 */
	function &getPlugin () {
		if (!is_object($this->_plugin)) {
			$configuration =& new ConfigurationProperties;
			$configuration->addProperty('plugin_dir', $dir = MYDIR."/plugins");
			$configuration->addProperty('plugin_path', $path = MYPATH."/plugins");
	
			$this->_plugin =& Plugin::newInstance($this->_asset, $configuration);
		}
		return $this->_plugin;
	}
	
	/**
	 * Answer the title that should be displayed for this node.
	 * 
	 * @return string
	 * @access public
	 * @since 1/19/06
	 */
	function getTitle () {
		$plugin =& $this->getPlugin();
		if ($plugin->getPluginTitleMarkup())
			return $plugin->getPluginTitleMarkup();
		else
			return "";
	}
}