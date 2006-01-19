<?php
/**
 * @since 1/19/06
 * @package segue.display
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PluginNodeRenderer.class.php,v 1.5 2006/01/19 21:39:25 cws-midd Exp $
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
 * @version $Id: PluginNodeRenderer.class.php,v 1.5 2006/01/19 21:39:25 cws-midd Exp $
 */
class PluginNodeRenderer
	extends NodeRenderer
{
	
	/**
	 * Answer the GUI component for the navegational item.
	 * 
	 * @param integer $level The Navigational level to use, 1=big, >1=smaller
	 * @return object Component
	 * @access public
	 * @since 1/19/06
	 */
	function &renderNavComponent ($level = 1) {
		return $this->renderTargetComponent($level);
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
		$configuration =& new ConfigurationProperties;
		$configuration->addProperty('plugin_dir', $dir = MYDIR."/plugins");
		$configuration->addProperty('plugin_path', $path = MYPATH."/plugins");

		$plugin =& Plugin::newInstance($this->_asset, $configuration);
		
		$assetId =& $this->_asset->getId();
		
		ob_start();
		
		print AjaxPlugin::getPluginSystemJavascript();
		
		if (!is_object($plugin)) {
			print $plugin;
		} else {
			print "\n<div id='plugin:".$plugin->getId()."'>";
			
			$harmoni =& Harmoni::instance();
			$harmoni->request->startNamespace(
				get_class($plugin).':'.$assetId->getIdString());
			$baseUrl =& $harmoni->request->mkURL();
			print $plugin->executeAndGetMarkup($baseUrl);
			$harmoni->request->endNamespace();
			
			print "\n</div>";
		}
				
		$component =& new Block(ob_get_clean(), STANDARD_BLOCK);
		return $component;
	}
}