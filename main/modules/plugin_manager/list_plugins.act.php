<?php
/**
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list_plugins.act.php,v 1.2 2007/09/04 15:07:43 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/XmlAction.class.php");

/**
 * 
 * 
 * @package segue.modules.home
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: list_plugins.act.php,v 1.2 2007/09/04 15:07:43 adamfranco Exp $
 */
class list_pluginsAction 
	extends XmlAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function execute () {
		$this->start();
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('plugin_manager');
		
		
		$pluginManager = Services::getService("Plugs");
		$types = $pluginManager->getEnabledPlugins();
		
		foreach ($types as $type) {
			print "\n\t<pluginType typeString=\"".$type->asString()."\">";
			print "\n\t\t<domain>".$type->getDomain()."</domain>";
			print "\n\t\t<authority>".$type->getAuthority()."</authority>";
			print "\n\t\t<keyword>".$type->getKeyword()."</keyword>";
			print "\n\t\t<description><![CDATA[".$type->getDescription()."]]></description>";
			print "\n\t\t<icon><![CDATA[".$pluginManager->getPluginIconUrl($type)."]]></icon>";
			print "\n\t</pluginType>";
		}
		
		$harmoni->request->endNamespace();
		$this->end();
	}
}

?>